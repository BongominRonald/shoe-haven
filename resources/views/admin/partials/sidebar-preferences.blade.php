@php
    $user = auth()->user();
    $prefs = $user?->sidebar_prefs ?? [];
@endphp

<div class="modal fade" id="sidebarPrefsModal" tabindex="-1" aria-labelledby="sidebarPrefsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sh-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="sidebarPrefsModalLabel">Sidebar Preferences</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.preferences.update') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">Collapse sidebar</div>
                            <span class="sh-caption">Show icons only on desktop</span>
                        </div>
                        <label class="sh-switch">
                            <input type="checkbox" name="sidebar_collapsed" value="1" @checked($prefs['sidebar_collapsed'] ?? false)>
                            <span class="sh-switch-track"></span>
                        </label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">Show help tips</div>
                            <span class="sh-caption">Inline hints across the admin</span>
                        </div>
                        <label class="sh-switch">
                            <input type="checkbox" name="show_help_tips" value="1" @checked($prefs['show_help_tips'] ?? true)>
                            <span class="sh-switch-track"></span>
                        </label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">Dense tables</div>
                            <span class="sh-caption">Compact table rows</span>
                        </div>
                        <label class="sh-switch">
                            <input type="checkbox" name="dense_tables" value="1" @checked($prefs['dense_tables'] ?? false)>
                            <span class="sh-switch-track"></span>
                        </label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                        <div>
                            <div class="fw-semibold small">Confirm deletions</div>
                            <span class="sh-caption">Ask before destructive actions</span>
                        </div>
                        <label class="sh-switch">
                            <input type="checkbox" name="confirm_deletes" value="1" @checked($prefs['confirm_deletes'] ?? true)>
                            <span class="sh-switch-track"></span>
                        </label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between py-2">
                        <div>
                            <div class="fw-semibold small">Unread messages badge</div>
                            <span class="sh-caption">Show unread count in the top bar</span>
                        </div>
                        <label class="sh-switch">
                            <input type="checkbox" name="unread_badge" value="1" @checked($prefs['unread_badge'] ?? true)>
                            <span class="sh-switch-track"></span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="sh-btn sh-btn-light"
                            onclick="document.getElementById('resetPrefsForm').submit()">
                        Reset defaults
                    </button>
                    <button type="submit" class="sh-btn sh-btn-primary" data-loading>Save preferences</button>
                </div>
            </form>
            <form id="resetPrefsForm" action="{{ route('admin.preferences.reset') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>
