<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    private const SENT_MESSAGE = 'If that email matches a staff account, we have sent a password reset link. Please check your inbox (and spam folder).';

    public function showRequestForm()
    {
        return view('admin.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:150'],
        ]);

        // Deactivated accounts get the same generic response so the form
        // cannot be used to discover which addresses belong to staff.
        $user = User::where('email', $request->input('email'))->first();

        if ($user && ! $user->is_active) {
            return back()->with('status', self::SENT_MESSAGE);
        }

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'email' => 'We could not send the reset email right now. Please try again later or contact the club office on info@1000hillsrugby.rw.',
            ]);
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()->withErrors([
                'email' => 'A reset link was sent recently. Please wait a minute before requesting another one.',
            ]);
        }

        return back()->with('status', self::SENT_MESSAGE);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:150'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Sign the account out everywhere it was already signed in.
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
                }

                Activity::log('updated', $user, 'Reset their password via email link');
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('admin.login')
                ->with('status', 'Your password has been reset. You can now sign in with your new password.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $this->failureMessage($status)]);
    }

    private function failureMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'This password reset link is invalid or has expired. Please request a new one.',
            Password::INVALID_USER => 'We could not find a staff account with that email address.',
            default => 'We could not reset your password. Please request a new reset link.',
        };
    }
}
