<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AuthUserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\RedirectResponse;
use Exception;

class AuthUserProviderController extends Controller
{
    /**
     * Supported social providers
     */
    private const SUPPORTED_PROVIDERS = ['github', 'google', 'facebook'];

    /**
     * Redirect to social provider authentication page
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        Log::info('Social auth redirect initiated', [
            'provider' => $provider,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            Log::warning('Unsupported provider attempted', [
                'provider' => $provider,
                'supported_providers' => self::SUPPORTED_PROVIDERS
            ]);
            abort(404, 'Provider not supported');
        }

        try {
            $redirectUrl = Socialite::driver($provider)->redirect();
            Log::info('Successfully redirected to provider', [
                'provider' => $provider
            ]);
            return $redirectUrl;
        } catch (Exception $e) {
            Log::error('Failed to redirect to provider', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Handle social provider callback
     */
    public function handleProviderCallback(string $provider): RedirectResponse
    {
        Log::info('Social auth callback received', [
            'provider' => $provider,
            'request_params' => request()->all(),
            'user_ip' => request()->ip()
        ]);

        if (!in_array($provider, self::SUPPORTED_PROVIDERS)) {
            Log::warning('Unsupported provider callback attempted', [
                'provider' => $provider,
                'supported_providers' => self::SUPPORTED_PROVIDERS
            ]);
            abort(404, 'Provider not supported');
        }

        try {
            Log::info('Attempting to get social user data', ['provider' => $provider]);
            
            $socialUser = Socialite::driver($provider)->user();
            
            Log::info('Social user data retrieved successfully', [
                'provider' => $provider,
                'social_user_id' => $socialUser->getId(),
                'social_user_email' => $socialUser->getEmail(),
                'social_user_name' => $socialUser->getName(),
                'social_user_nickname' => $socialUser->getNickname(),
                'has_token' => !empty($socialUser->token),
                'has_refresh_token' => !empty($socialUser->refreshToken)
            ]);

            Log::info('Finding or creating user', [
                'provider' => $provider,
                'social_email' => $socialUser->getEmail()
            ]);

            $user = $this->findOrCreateUser($socialUser, $provider);
            
            Log::info('User found/created successfully', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'is_new_user' => $user->wasRecentlyCreated ?? false
            ]);

            Log::info('Updating provider data', [
                'user_id' => $user->id,
                'provider' => $provider
            ]);

            $this->updateProviderData($user, $socialUser, $provider);

            Log::info('Attempting to login user', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);

            Auth::login($user);

            // Verify login was successful
            if (Auth::check()) {
                Log::info('User successfully authenticated', [
                    'user_id' => Auth::id(),
                    'authenticated_user_email' => Auth::user()->email,
                    'provider' => $provider
                ]);
            } else {
                Log::error('Authentication failed - user not logged in after Auth::login()', [
                    'user_id' => $user->id,
                    'provider' => $provider
                ]);
            }

            Log::info('Redirecting to intended destination');
            return redirect()->intended('/dashboard');

        } catch (Exception $e) {
            Log::error('Social authentication failed', [
                'provider' => $provider,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString(),
                'request_params' => request()->all()
            ]);

            return redirect('/login')->withErrors([
                'social_auth' => 'Authentication failed. Please try again.'
            ]);
        }
    }

    /**
     * Find existing user or create new one
     */
    private function findOrCreateUser($socialUser, string $provider): User
    {
        Log::info('Searching for existing user by email', [
            'email' => $socialUser->getEmail(),
            'provider' => $provider
        ]);

        // First, try to find user by email
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            Log::info('Existing user found by email', [
                'user_id' => $existingUser->id,
                'user_email' => $existingUser->email,
                'existing_provider' => $existingUser->provider ?? 'none',
                'status' => 'active'
            ]);
            return $existingUser;
        }

        Log::info('No existing user found, creating new user', [
            'email' => $socialUser->getEmail(),
            'provider' => $provider
        ]);

        // If no user found by email, create new user
        [$firstName, $lastName] = $this->splitName($socialUser->getName());
        $username = $this->generateUniqueUsername($socialUser, $provider);

        Log::info('Parsed user data for creation', [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'username' => $username,
            'provider' => $provider,
            'provider_id' => $socialUser->getId()
        ]);

        try {
            $newUser = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'email_verified_at' => now(),
                'avatar' => $socialUser->getAvatar(),
                'username' => $username,
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);

            Log::info('New user created successfully', [
                'new_user_id' => $newUser->id,
                'new_user_email' => $newUser->email,
                'provider' => $provider
            ]);

            return $newUser;

        } catch (Exception $e) {
            Log::error('Failed to create new user', [
                'error' => $e->getMessage(),
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update or create provider-specific data
     */
    private function updateProviderData(User $user, $socialUser, string $provider): void
    {
        Log::info('Updating provider data', [
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            'has_token' => !empty($socialUser->token),
            'has_refresh_token' => !empty($socialUser->refreshToken),
            'token_expires_in' => $socialUser->expiresIn ?? 'no_expiry'
        ]);

        try {
            $providerData = AuthUserProvider::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'provider' => $provider,
                ],
                [
                    'provider_id' => $socialUser->getId(),
                    'token' => $socialUser->token,
                    'refresh_token' => $socialUser->refreshToken ?? null,
                    'token_expires_at' => $socialUser->expiresIn
                        ? now()->addSeconds($socialUser->expiresIn)
                        : null,
                ]
            );

            Log::info('Provider data updated successfully', [
                'auth_provider_id' => $providerData->id,
                'user_id' => $user->id,
                'provider' => $provider,
                'was_recently_created' => $providerData->wasRecentlyCreated
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update provider data', [
                'user_id' => $user->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Split full name into first and last name
     */
    private function splitName(?string $name): array
    {
        if (!$name) {
            Log::debug('No name provided for splitting');
            return [null, null];
        }

        $parts = explode(' ', trim($name));
        $firstName = array_shift($parts);
        $lastName = implode(' ', $parts);

        Log::debug('Name split into components', [
            'original_name' => $name,
            'first_name' => $firstName,
            'last_name' => $lastName ?: null
        ]);

        return [$firstName, $lastName ?: null];
    }

    /**
     * Generate unique username based on provider data
     */
    private function generateUniqueUsername($socialUser, string $provider): ?string
    {
        $username = null;

        switch ($provider) {
            case 'github':
                $username = $socialUser->getNickname();
                Log::debug('Using GitHub nickname as username', [
                    'nickname' => $username,
                    'provider' => $provider
                ]);
                break;
            case 'google':
            case 'facebook':
                $emailPart = explode('@', $socialUser->getEmail())[0];
                $username = $emailPart;
                Log::debug('Generated username from email', [
                    'email' => $socialUser->getEmail(),
                    'generated_username' => $username,
                    'provider' => $provider
                ]);
                break;
        }

        if ($username && User::where('username', $username)->exists()) {
            Log::info('Username already exists, generating unique variant', [
                'original_username' => $username,
                'provider' => $provider
            ]);

            $baseUsername = $username;
            $counter = 1;

            do {
                $username = $baseUsername . $counter;
                $counter++;
            } while (User::where('username', $username)->exists());

            Log::info('Generated unique username', [
                'original_username' => $baseUsername,
                'unique_username' => $username,
                'attempts' => $counter - 1
            ]);
        }

        return $username;
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $userEmail = Auth::user()->email ?? 'unknown';

        Log::info('User logout initiated', [
            'user_id' => $userId,
            'user_email' => $userEmail,
            'user_ip' => $request->ip()
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out successfully', [
            'former_user_id' => $userId,
            'former_user_email' => $userEmail
        ]);

        return redirect('/');
    }
}