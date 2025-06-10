import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

type RegisterForm = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
};

const SocialButton = ({ provider, icon, children }: { provider: string; icon: string; children: React.ReactNode }) => (
    <Button
        type="button"
        variant="outline"
        className="h-11 w-full border-gray-300 bg-white font-medium text-gray-700 transition-colors hover:bg-gray-50"
        onClick={() => (window.location.href = `/auth/redirect/${provider}`)}
    >
        <img src={icon} alt={`${provider} icon`} className="mr-3 h-5 w-5" />
        {children}
    </Button>
);

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm<Required<RegisterForm>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Create your account" description="Get started by creating your account">
            <Head title="Register" />

            <div className="mx-auto w-full max-w-sm">
                {/* Social Registration Buttons */}
                <div className="mb-6 space-y-3">
                    <SocialButton provider="google" icon="https://developers.google.com/identity/images/g-logo.png">
                        Continue with Google
                    </SocialButton>

                    <SocialButton provider="github" icon="https://github.githubassets.com/assets/GitHub-Mark-ea2971cee799.png">
                        Continue with GitHub
                    </SocialButton>

                    <SocialButton provider="facebook" icon="https://static.xx.fbcdn.net/rsrc.php/v3/yX/r/Kvo5FesWVKX.png">
                        Continue with Facebook
                    </SocialButton>
                </div>

                {/* Divider */}
                <div className="relative mb-6">
                    <div className="absolute inset-0 flex items-center">
                        <span className="w-full border-t border-gray-300" />
                    </div>
                    <div className="relative flex justify-center text-sm">
                        <span className="bg-white px-4 font-medium text-gray-500">or continue with email</span>
                    </div>
                </div>

                <form className="space-y-5" onSubmit={submit}>
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name" className="text-sm font-medium text-gray-700">
                                Full name
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                required
                                autoFocus
                                tabIndex={1}
                                autoComplete="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                disabled={processing}
                                placeholder="Enter your full name"
                                className="h-11 border-gray-300 px-4 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email" className="text-sm font-medium text-gray-700">
                                Email address
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                tabIndex={2}
                                autoComplete="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                disabled={processing}
                                placeholder="Enter your email"
                                className="h-11 border-gray-300 px-4 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <InputError message={errors.email} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password" className="text-sm font-medium text-gray-700">
                                Password
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                tabIndex={3}
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(e) => setData('password', e.target.value)}
                                disabled={processing}
                                placeholder="Create a strong password"
                                className="h-11 border-gray-300 px-4 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <InputError message={errors.password} />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password_confirmation" className="text-sm font-medium text-gray-700">
                                Confirm password
                            </Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                tabIndex={4}
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                disabled={processing}
                                placeholder="Confirm your password"
                                className="h-11 border-gray-300 px-4 focus:border-blue-500 focus:ring-blue-500"
                            />
                            <InputError message={errors.password_confirmation} />
                        </div>

                        <Button
                            type="submit"
                            className="h-11 w-full bg-blue-600 font-medium text-white shadow-sm transition-colors hover:bg-blue-700"
                            tabIndex={5}
                            disabled={processing}
                        >
                            {processing && <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />}
                            Create account
                        </Button>
                    </div>

                    <div className="text-center text-xs leading-relaxed text-gray-500">
                        By creating an account, you agree to our{' '}
                        <TextLink href="/terms" className="text-blue-600 hover:text-blue-500">
                            Terms of Service
                        </TextLink>{' '}
                        and{' '}
                        <TextLink href="/privacy" className="text-blue-600 hover:text-blue-500">
                            Privacy Policy
                        </TextLink>
                        .
                    </div>
                </form>

                <div className="mt-6 text-center">
                    <span className="text-sm text-gray-600">
                        Already have an account?{' '}
                        <TextLink href={route('login')} className="font-medium text-blue-600 hover:text-blue-500" tabIndex={6}>
                            Sign in
                        </TextLink>
                    </span>
                </div>
            </div>
        </AuthLayout>
    );
}
