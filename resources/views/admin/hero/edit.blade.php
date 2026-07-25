@extends('admin.layouts.admin')

@section('title', 'Hero Section — ' . config('app.name', 'Shoe Haven'))
@section('page-title', 'Hero Section')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('admin.hero.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-md-8">
                    <label class="form-label fw-medium">Headline</label>
                    <input type="text" name="headline" class="form-control @error('headline') is-invalid @enderror"
                           value="{{ old('headline', $hero->headline ?? '') }}" required>
                    @error('headline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">HTML is allowed (e.g. <code>&lt;span&gt;</code> for highlights).</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-medium">Hero Image</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($hero->image ?? false)
                        <img src="{{ asset($hero->image) }}" class="img-fluid rounded mt-2" style="max-height: 100px; object-fit: cover;" alt="Current hero image">
                    @endif
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Subtitle</label>
                    <textarea name="subtitle" rows="3" class="form-control @error('subtitle') is-invalid @enderror" required>{{ old('subtitle', $hero->subtitle ?? '') }}</textarea>
                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Button Text</label>
                    <input type="text" name="button_text" class="form-control @error('button_text') is-invalid @enderror"
                           value="{{ old('button_text', $hero->button_text ?? 'Shop Now') }}" required>
                    @error('button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Button URL</label>
                    <input type="text" name="button_url" class="form-control @error('button_url') is-invalid @enderror"
                           value="{{ old('button_url', $hero->button_url ?? '/shop') }}" required>
                    @error('button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Secondary Button Text</label>
                    <input type="text" name="secondary_button_text" class="form-control @error('secondary_button_text') is-invalid @enderror"
                           value="{{ old('secondary_button_text', $hero->secondary_button_text ?? 'Explore Collection') }}" required>
                    @error('secondary_button_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-medium">Secondary Button URL</label>
                    <input type="text" name="secondary_button_url" class="form-control @error('secondary_button_url') is-invalid @enderror"
                           value="{{ old('secondary_button_url', $hero->secondary_button_url ?? '/shop') }}" required>
                    @error('secondary_button_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Stats (up to 3)</h6>
            <div class="row g-3">
                @php $s = old('stats', $hero->stats ?? [['value' => '15K+', 'label' => 'Happy Customers'], ['value' => '500+', 'label' => 'Shoe Styles'], ['value' => '4.9', 'label' => 'Average Rating']]) @endphp
                @for ($i = 0; $i < 3; $i++)
                    <div class="col-md-4">
                        <div class="border rounded p-3">
                            <div class="mb-2">
                                <label class="form-label small mb-0">Value</label>
                                <input type="text" name="stat_value_{{ $i }}" class="form-control form-control-sm"
                                       value="{{ is_array($s) && isset($s[$i]['value']) ? $s[$i]['value'] : ($s[$i] ?? '') }}">
                            </div>
                            <div>
                                <label class="form-label small mb-0">Label</label>
                                <input type="text" name="stat_label_{{ $i }}" class="form-control form-control-sm"
                                       value="{{ is_array($s) && isset($s[$i]['label']) ? $s[$i]['label'] : '' }}">
                            </div>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-sh-orange">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
