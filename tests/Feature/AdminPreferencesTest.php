<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPreferencesTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $admin = User::factory()->create();
        UserRole::create(['user_id' => $admin->id, 'role' => 'admin']);

        return $admin;
    }

    private function makeRegularUser(): User
    {
        return User::factory()->create();
    }

    public function test_guest_is_redirected_from_preferences_update(): void
    {
        $this->post(route('admin.preferences.update'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_update_preferences(): void
    {
        $user = $this->makeRegularUser();

        $this->actingAs($user)
            ->post(route('admin.preferences.update'), ['sidebar_collapsed' => true])
            ->assertForbidden();
    }

    public function test_preferences_modal_is_rendered_in_admin_layout(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('id="sidebarPrefsModal"', false)
            ->assertSee('Sidebar Preferences')
            ->assertSee('Collapse sidebar')
            ->assertSee('Show help tips')
            ->assertSee('Dense tables')
            ->assertSee('Confirm deletions')
            ->assertSee('Unread messages badge');
    }

    public function test_update_persists_sidebar_prefs(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.preferences.update'), [
                'sidebar_collapsed' => 1,
                'show_help_tips' => 0,
                'dense_tables' => 1,
                'confirm_deletes' => 1,
                'unread_badge' => 0,
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Preferences saved.');

        $this->assertSame(
            ['sidebar_collapsed' => true, 'show_help_tips' => false, 'dense_tables' => true, 'confirm_deletes' => true, 'unread_badge' => false],
            $admin->fresh()->sidebar_prefs
        );
    }

    public function test_update_merges_with_existing_prefs(): void
    {
        $admin = $this->makeAdmin();
        $admin->update(['sidebar_prefs' => ['sidebar_collapsed' => true, 'color' => 'dark']]);

        $this->actingAs($admin)
            ->post(route('admin.preferences.update'), ['dense_tables' => 1]);

        $this->assertTrue($admin->fresh()->sidebar_prefs['sidebar_collapsed']);
        $this->assertTrue($admin->fresh()->sidebar_prefs['dense_tables']);
        $this->assertSame('dark', $admin->fresh()->sidebar_prefs['color']);
    }

    public function test_update_rejects_unknown_keys(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
            ->post(route('admin.preferences.update'), ['nonsense_key' => 'x', 'sidebar_collapsed' => 1])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertArrayNotHasKey('nonsense_key', $admin->fresh()->sidebar_prefs);
    }

    public function test_reset_restores_defaults(): void
    {
        $admin = $this->makeAdmin();
        $admin->update(['sidebar_prefs' => ['sidebar_collapsed' => true, 'dense_tables' => true]]);

        $this->actingAs($admin)
            ->post(route('admin.preferences.reset'))
            ->assertRedirect()
            ->assertSessionHas('status');

        $prefs = $admin->fresh()->sidebar_prefs;
        $this->assertFalse($prefs['sidebar_collapsed']);
        $this->assertFalse($prefs['dense_tables']);
    }

    public function test_user_sidebar_pref_helper_returns_defaults(): void
    {
        $admin = $this->makeAdmin();

        $this->assertTrue($admin->sidebarPref('show_help_tips', true));
        $this->assertNull($admin->sidebarPref('missing_key'));
        $this->assertFalse($admin->sidebarPref('sidebar_collapsed', false));
    }

    public function test_sidebar_renders_nav_groups_from_config(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        foreach (config('admin-nav') as $group) {
            $response->assertSee($group['label']);
            foreach ($group['items'] as $item) {
                $response->assertSee($item['label'], false);
            }
        }
    }

    public function test_layout_shows_unread_messages_badge(): void
    {
        $admin = $this->makeAdmin();
        ContactMessage::create([
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Hello',
            'message' => 'Need help with an order',
            'is_read' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('bi-bell', false);
    }
}