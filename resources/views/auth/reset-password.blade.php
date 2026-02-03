<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-lg">
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-2xl overflow-hidden">
                <div class="px-8 py-6 md:px-12 md:py-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('auth.forgot_section.title') }}</h3>
                    </div>

                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">{{ __('Please provide a new secure password for your account.') }}</p>

                    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                        @csrf

                        <!-- Password Reset Token -->
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <!-- Email Address -->
                        <div>
                            <x-text-input id="email" class="mt-2" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="{{ __('auth.placeholder.email') }}" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600 text-sm" />
                        </div>

                        <!-- Password -->
                        <div>
                            <x-input-label for="password" :value="__('Password')" />
                            <x-text-input id="password" class="mt-2" type="password" name="password" required autocomplete="new-password"  />
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600 text-sm" />
                            <div id="password-rules" class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded text-sm text-gray-700">
                                <div class="font-semibold mb-1">{{ __('auth.password.rules.title') }}</div>
                                <ul class="list-disc ml-5">
                                    <li id="rule-length">{{ __('auth.password.rules.length') }}</li>
                                    <li id="rule-letters">{{ __('auth.password.rules.letters') }}</li>
                                    <li id="rule-numbers">{{ __('auth.password.rules.numbers') }}</li>
                                    <li id="rule-special">{{ __('auth.password.rules.special') }}</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                            <x-text-input id="password_confirmation" class="mt-2" type="password" name="password_confirmation" required autocomplete="new-password"  />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-600 text-sm" />
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-300 hover:underline">{{ __('auth.forgot_section.back_to_login') }}</a>
                            <x-primary-button class="bg-[#4f6ba3] hover:bg-[#3a5680]">
                                {{ __('Reset Password') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function(){
            const pwd = document.getElementById('password');
            if (!pwd) return;

            const ruleLength = document.getElementById('rule-length');
            const ruleLetters = document.getElementById('rule-letters');
            const ruleNumbers = document.getElementById('rule-numbers');
            const ruleSpecial = document.getElementById('rule-special');

            function validate(value){
                const hasLength = value.length >= 8;
                const hasLetters = /[A-Za-z]/.test(value);
                const hasNumbers = /[0-9]/.test(value);
                const hasSpecial = /[^A-Za-z0-9]/.test(value);

                ruleLength.style.textDecoration = hasLength ? 'none' : 'line-through';
                ruleLetters.style.textDecoration = hasLetters ? 'none' : 'line-through';
                ruleNumbers.style.textDecoration = hasNumbers ? 'none' : 'line-through';
                ruleSpecial.style.textDecoration = hasSpecial ? 'none' : 'line-through';
            }

            pwd.addEventListener('input', function(e){
                validate(e.target.value);
            });

            // initial
            validate(pwd.value || '');
        })();
    </script>
</x-guest-layout>

