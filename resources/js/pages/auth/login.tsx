import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type LoginForm = {
    email: string;
    password: string;
    remember: boolean;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

const SocialButton = ({ provider, icon, children }: { provider: string; icon: string; children: React.ReactNode }) => (
    <Button
        type="button"
        variant="outline"
        className="w-full h-11 bg-white hover:bg-gray-50 border-gray-300 text-gray-700 font-medium transition-colors"
        onClick={() => window.location.href = `/auth/redirect/${provider}`}
    >
        <img src={icon} alt={`${provider} icon`} className="w-5 h-5 mr-3" />
        {children}
    </Button>
);

export default function Login({ status, canResetPassword }: LoginProps) {
    const { data, setData, post, processing, errors, reset } = useForm<Required<LoginForm>>({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout 
            title="Welcome back" 
            description="Sign in to your account to continue"
        >
            <Head title="Log in" />

            <div className="w-full max-w-sm mx-auto">
                {status && (
                    <div className="mb-6 p-4 text-center text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg">
                        {status}
                    </div>
                )}

                {/* Social Login Buttons */}
                <div className="space-y-3 mb-6">
                    <SocialButton 
                        provider="google" 
                        icon="https://developers.google.com/identity/images/g-logo.png"
                    >
                        Continue with Google
                    </SocialButton>
                    
                    <SocialButton 
                        provider="github" 
                        icon="https://github.githubassets.com/assets/GitHub-Mark-ea2971cee799.png"
                    >
                        Continue with GitHub
                    </SocialButton>
                    
                    <SocialButton 
                        provider="facebook" 
                        icon="https://static.xx.fbcdn.net/rsrc.php/v3/yX/r/Kvo5FesWVKX.png"
                    >
                        Continue with Facebook
                    </SocialButton>
                </div>

                {/* Divider */}
                <div className="relative mb-6">
                    <div className="absolute inset-0 flex items-center">
                        <span className="w-full border-t border-gray-300" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                        <span className="bg-white px-4 text-gray-500 font-medium">or continue with email</span>
                    </div>
                </div>

                <form className="space-y-5" onSubmit={submit}>
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email" className="text-sm font-medium text-gray-700">
                                Email address
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="Enter your email"
                                className="h-11 px-4 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between">
                                <Label htmlFor="password" className="text-sm font-medium text-gray-700">
                                    Password
                                </Label>
                                {canResetPassword && (
                                    <TextLink 
                                        href={route('password.request')} 
                                        className="text-sm text-blue-600 hover:text-blue-500 font-medium"
                                        tabIndex={5}
                                    >
                                        Forgot password?
                                    </TextLink>
                                )}
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabIndex={2}
                                autoComplete="current-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                placeholder="Enter your password"
                                className="h-11 px-4 border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="flex items-center space-x-3">
                            <Checkbox
                                id="remember"
                                name="remember"
                                checked={data.remember}
                                onClick={() => setData('remember', !data.remember)}
                                tabIndex={3}
                                className="border-gray-300"
                            />
                            <Label htmlFor="remember" className="text-sm text-gray-700">
                                Remember me for 30 days
                            </Label>
                        </div>

                        <Button 
                            type="submit" 
                            className="w-full h-11 bg-blue-600 hover:bg-blue-700 text-white font-medium shadow-sm transition-colors" 
                            tabIndex={4} 
                            disabled={processing}
                        >
                            {processing && <LoaderCircle className="h-4 w-4 animate-spin mr-2" />}
                            Sign in
                        </Button>
                    </div>
                </form>

                <div className="mt-6 text-center">
                    <span className="text-sm text-gray-600">
                        Don't have an account?{' '}
                        <TextLink 
                            href={route('register')} 
                            className="text-blue-600 hover:text-blue-500 font-medium"
                            tabIndex={6}
                        >
                            Create account
                        </TextLink>
                    </span>
                </div>
            </div>
        </AuthLayout>
    );
}