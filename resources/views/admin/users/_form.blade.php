@php($isEdit = $user->exists)
<div class="row g-4">
    <div class="col-xl-8">
        <div class="card-panel">
            <div class="card-panel-header"><h5 class="mb-0">Account details</h5></div>
            <div class="card-panel-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Full name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" class="form-control" placeholder="optional">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="role">Role <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="editor" @selected(old('role', $user->role) === 'editor')>Editor — manage content</option>
                            <option value="coach" @selected(old('role', $user->role) === 'coach')>Coach — squad players, sessions and reports</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>Administrator — full access</option>
                        </select>
                        <div class="form-text">Coaches manage their own squad's players, sessions, attendance and reports.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="team_id">Coach squad</label>
                        <select id="team_id" name="team_id" class="form-select">
                            <option value="">Not a squad coach</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" @selected((string) old('team_id', $user->team_id) === (string) $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Required when the role is Coach. They only see players in this squad.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password">Password {{ $isEdit ? '(leave blank to keep current)' : '*' }}</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" @required(! $isEdit)>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="password_confirmation">Confirm password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" @required(! $isEdit)>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card-panel mb-4">
            <div class="card-panel-header"><h5 class="mb-0">Profile picture</h5></div>
            <div class="card-panel-body">
                @if($user->avatar)
                    <img src="{{ media_url($user->avatar) }}" alt="" class="w-100 rounded mb-2" style="height:200px;object-fit:cover;">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remove_avatar" name="remove_avatar" value="1">
                        <label class="form-check-label small" for="remove_avatar">Remove current picture</label>
                    </div>
                @endif
                <label class="form-label" for="avatar">Profile picture @unless($isEdit)<span class="text-danger">*</span>@endunless</label>
                <input type="file" id="avatar" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*" data-preview="#avatarPreview" @required(! $isEdit)>
                @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <img id="avatarPreview" class="w-100 rounded mt-2 d-none" alt="Preview" style="height:200px;object-fit:cover;">
                <div class="form-text">A square photo works best. Max 8 MB.{{ $isEdit ? '' : ' Required when creating an account.' }}</div>
            </div>
        </div>
        <div class="card-panel">
            <div class="card-panel-header"><h5 class="mb-0">Access</h5></div>
            <div class="card-panel-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $user->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Account active</label>
                </div>
                <p class="text-muted small">Deactivated users cannot sign in to the admin panel.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-admin-forest" type="submit"><i class="bi bi-check2 me-1"></i>{{ $isEdit ? 'Save changes' : 'Create user' }}</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-admin-outline">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
