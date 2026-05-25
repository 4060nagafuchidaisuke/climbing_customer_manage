<nav x-data="{ open: false }" class="bg-slate-800 border-b border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">

                {{-- ロゴ --}}
                <a href="{{ route('dashboard') }}" class="text-white font-bold text-xl tracking-wider me-10">
                    <img src="{{ asset('images/HAZY_Bolder_logos.png') }}" alt="HAZY BOULDER"class="h-10 w-auto brightness-0 invert">
                </a>

                {{-- ナビリンク（PC） --}}
                <div class="hidden sm:flex sm:items-center sm:space-x-2">
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-2 rounded-md text-sm font-medium transition duration-150
                              {{ request()->routeIs('dashboard') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        ダッシュボード
                    </a>
                    <a href="#"
                       class="px-3 py-2 rounded-md text-sm font-medium transition duration-150
                              {{ request()->routeIs('members.*') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        会員管理
                    </a>
                    <a href="#"
                       class="px-3 py-2 rounded-md text-sm font-medium transition duration-150
                              {{ request()->routeIs('visits.*') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                        在店中
                    </a>

                    {{-- 管理者のみ表示 --}}
                    @if(Auth::user()->role === \App\Enums\StaffRole::ADMIN)
                        <a href="#"
                           class="px-3 py-2 rounded-md text-sm font-medium transition duration-150
                                  {{ request()->routeIs('staff.*') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                            スタッフ管理
                        </a>
                    @endif
                </div>
            </div>

            {{-- ユーザードロップダウン（PC） --}}
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-slate-600 text-sm font-medium rounded-md text-slate-300 bg-slate-700 hover:text-white hover:bg-slate-600 focus:outline-none transition duration-150">
                            {{ Auth::user()->name }}
                            <svg class="ms-1 fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            プロフィール
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                ログアウト
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- ハンバーガーメニュー（スマホ） --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-700 focus:outline-none transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- スマホメニュー展開時 --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-md text-base font-medium
                      {{ request()->routeIs('dashboard') ? 'bg-slate-600 text-white' : 'text-slate-300 hover:bg-slate-700 hover:text-white' }}">
                ダッシュボード
            </a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-700 hover:text-white">
                会員管理
            </a>
            <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-700 hover:text-white">
                在店中
            </a>
            @if(Auth::user()->role === \App\Enums\StaffRole::ADMIN)
                <a href="#" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-700 hover:text-white">
                    スタッフ管理
                </a>
            @endif
        </div>
        <div class="pt-4 pb-1 border-t border-slate-700">
            <div class="px-4">
                <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-slate-400">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1 px-2">
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-700 hover:text-white">
                    プロフィール
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-base font-medium text-slate-300 hover:bg-slate-700 hover:text-white">
                        ログアウト
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>