<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                /*! tailwindcss v4.0.7 | MIT License | https://tailwindcss.com */@layer theme{:root,:host{--font-sans:'Instrument Sans',ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";--font-serif:ui-serif,Georgia,Cambria,"Times New Roman",Times,serif;--font-mono:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;--color-red-50:oklch(.971 .013 17.38);--color-red-100:oklch(.936 .032 17.717);--color-red-200:oklch(.885 .062 18.334);--color-red-300:oklch(.808 .114 19.571);--color-red-400:oklch(.704 .191 22.216);--color-red-500:oklch(.637 .237 25.331);--color-red-600:oklch(.577 .245 27.325);--color-red-700:oklch(.505 .213 27.518);--color-red-800:oklch(.444 .177 26.899);--color-red-900:oklch(.396 .141 25.723);--color-red-950:oklch(.258 .092 26.042);--color-orange-50:oklch(.98 .016 73.684);--color-orange-100:oklch(.954 .038 75.164);--color-orange-200:oklch(.901 .076 70.697);--color-orange-300:oklch(.837 .128 66.29);--color-orange-400:oklch(.75 .183 55.934);--color-orange-500:oklch(.705 .213 47.604);--color-orange-600:oklch(.646 .222 41.116);--color-orange-700:oklch(.553 .195 38.402);--color-orange-800:oklch(.47 .157 37.304);--color-orange-900:oklch(.408 .123 38.172);--color-orange-950:oklch(.266 .079 36.259);--color-amber-50:oklch(.987 .022 95.277);--color-amber-100:oklch(.962 .059 95.617);--color-amber-200:oklch(.924 .12 95.746);--color-amber-300:oklch(.879 .169 91.605);--color-amber-400:oklch(.828 .189 84.429);--color-amber-500:oklch(.769 .188 70.08);--color-amber-600:oklch(.666 .179 58.318);--color-amber-700:oklch(.555 .163 48.998);--color-amber-800:oklch(.473 .137 46.201);--color-amber-900:oklch(.414 .112 45.904);--color-amber-950:oklch(.279 .077 45.635);--color-yellow-50:oklch(.987 .026 102.212);--color-yellow-100:oklch(.973 .071 103.193);--color-yellow-200:oklch(.945 .129 101.54);--color-yellow-300:oklch(.905 .182 98.111);--color-yellow-400:oklch(.852 .199 91.936);--color-yellow-500:oklch(.795 .184 86.047);--color-yellow-600:oklch(.681 .162 75.834);--color-yellow-700:oklch(.554 .135 66.442);--color-yellow-800:oklch(.458 .099 63.175);--color-yellow-900:oklch(.403 .079 64.094);--color-yellow-950:oklch(.268 .052 64.126);--color-lime-50:oklch(.986 .031 120.757);--color-lime-100:oklch(.967 .067 122.328);--color-lime-200:oklch(.938 .127 124.321);--color-lime-300:oklch(.897 .196 126.665);--color-lime-400:oklch(.841 .238 128.85);--color-lime-500:oklch(.768 .253 131.01);--color-lime-600:oklch(.648 .2 131.592);--color-lime-700:oklch(.532 .157 131.589);--color-lime-800:oklch(.453 .124 130.933);--color-lime-900:oklch(.405 .101 131.063);--color-lime-950:oklch(.274 .072 132.109);--color-green-50:oklch(.982 .018 155.826);--color-green-100:oklch(.962 .044 156.746);--color-green-200:oklch(.925 .084 155.883);--color-green-300:oklch(.871 .15 154.449);--color-green-400:oklch(.792 .209 151.711);--color-green-500:oklch(.723 .219 149.579);--color-green-600:oklch(.627 .194 149.214);--color-green-700:oklch(.527 .154 150.069);--color-green-800:oklch(.448 .119 151.328);--color-green-900:oklch(.393 .095 152.535);--color-green-950:oklch(.266 .065 152.934);--color-emerald-50:oklch(.979 .021 166.113);--color-emerald-100:oklch(.95 .052 163.051);--color-emerald-200:oklch(.905 .105 162.484);--color-emerald-300:oklch(.845 .143 160.315);--color-emerald-400:oklch(.765 .177 159.441);--color-emerald-500:oklch(.696 .17 162.48);--color-emerald-600:oklch(.596 .145 163.225);--color-emerald-700:oklch(.508 .118 165.612);--color-emerald-800:oklch(.432 .095 166.913);--color-emerald-900:oklch(.378 .077 168.94);--color-emerald-950:oklch(.262 .048 172.552);--color-teal-50:oklch(.984 .014 180.72);--color-teal-100:oklch(.953 .051 180.237);--color-teal-200:oklch(.91 .096 180.474);--color-teal-300:oklch(.855 .138 181.065);--color-teal-400:oklch(.777 .152 181.912);--color-teal-500:oklch(.704 .14 182.503);--color-teal-600:oklch(.6 .118 184.704);--color-teal-700:oklch(.511 .096 186.391);--color-teal-800:oklch(.437 .078 188.216);--color-teal-900:oklch(.386 .063 190.418);--color-teal-950:oklch(.262 .041 192.548);--color-cyan-50:oklch(.984 .021 204.012);--color-cyan-100:oklch(.956 .045 203.388);--color-cyan-200:oklch(.917 .08 205.041);--color-cyan-300:oklch(.858 .126 205.984);--color-cyan-400:oklch(.789 .154 211.53);--color-cyan-500:oklch(.715 .143 215.221);--color-cyan-600:oklch(.609 .126 221.723);--color-cyan-700:oklch(.52 .105 223.128);--color-cyan-800:oklch(.438 .082 230.31);--color-cyan-900:oklch(.385 .069 237.005);--color-cyan-950:oklch(.263 .044 240.417);--color-sky-50:oklch(.977 .013 236.62);--color-sky-100:oklch(.951 .026 236.824);--color-sky-200:oklch(.901 .058 230.966);--color-sky-300:oklch(.828 .111 230.376);--color-sky-400:oklch(.746 .16 232.661);--color-sky-500:oklch(.682 .171 235.484);--color-sky-600:oklch(.588 .158 241.966);--color-sky-700:oklch(.5 .134 242.741);--color-sky-800:oklch(.428 .102 242.267);--color-sky-900:oklch(.379 .077 242.446);--color-sky-950:oklch(.258 .047 243.057);--color-blue-50:oklch(.97 .014 254.604);--color-blue-100:oklch(.932 .032 255.585);--color-blue-200:oklch(.882 .059 254.128);--color-blue-300:oklch(.809 .105 251.813);--color-blue-400:oklch(.707 .165 254.624);--color-blue-500:oklch(.623 .214 259.815);--color-blue-600:oklch(.546 .245 262.881);--color-blue-700:oklch(.488 .243 264.376);--color-blue-800:oklch(.424 .199 265.638);--color-blue-900:oklch(.379 .146 265.522);--color-blue-950:oklch(.282 .091 267.935);--color-indigo-50:oklch(.962 .018 272.314);--color-indigo-100:oklch(.93 .034 272.788);--color-indigo-200:oklch(.87 .065 274.039);--color-indigo-300:oklch(.785 .115 274.713);--color-indigo-400:oklch(.673 .182 276.935);--color-indigo-500:oklch(.585 .233 277.117);--color-indigo-600:oklch(.511 .262 276.966);--color-indigo-700:oklch(.457 .24 277.023);--color-indigo-800:oklch(.398 .195 277.366);--color-indigo-900:oklch(.359 .144 278.697);--color-indigo-950:oklch(.257 .09 281.288);--color-violet-50:oklch(.969 .016 293.729);--color-violet-100:oklch(.935 .034 294.112);--color-violet-200:oklch(.887 .062 298.474);--color-violet-300:oklch(.816 .112 300.537);--color-violet-400:oklch(.706 .191 303.68);--color-violet-500:oklch(.606 .25 306.457);--color-violet-600:oklch(.527 .279 306.931);--color-violet-700:oklch(.469 .242 307.12);--color-violet-800:oklch(.408 .204 306.52);--color-violet-900:oklch(.365 .143 307.039);--color-violet-950:oklch(.258 .089 310.47);--color-purple-50:oklch(.977 .014 322.504);--color-purple-100:oklch(.946 .033 323.537);--color-purple-200:oklch(.9 .063 327.273);--color-purple-300:oklch(.837 .128 333.85);--color-purple-400:oklch(.742 .198 333.683);--color-purple-500:oklch(.627 .265 339.239);--color-purple-600:oklch(.558 .288 341.385);--color-purple-700:oklch(.496 .255 341.917);--color-purple-800:oklch(.425 .208 342.553);--color-purple-900:oklch(.375 .158 343.193);--color-purple-950:oklch(.273 .098 343.893);--color-fuchsia-50:oklch(.977 .017 351.469);--color-fuchsia-100:oklch(.952 .037 351.568);--color-fuchsia-200:oklch(.907 .07 354.278);--color-fuchsia-300:oklch(.833 .145 352.448);--color-fuchsia-400:oklch(.74 .238 355.016);--color-fuchsia-500:oklch(.667 .295 351.15);--color-fuchsia-600:oklch(.591 .293 353.487);--color-fuchsia-700:oklch(.521 .245 353.444);--color-fuchsia-800:oklch(.452 .211 352.451);--color-fuchsia-900:oklch(.401 .17 352.698);--color-fuchsia-950:oklch(.293 .136 352.536);--color-pink-50:oklch(.971 .014 3.705);--color-pink-100:oklch(.948 .028 4.237);--color-pink-200:oklch(.899 .061 6.321);--color-pink-300:oklch(.823 .12 8.609);--color-pink-400:oklch(.718 .202 9.494);--color-pink-500:oklch(.656 .241 12.681);--color-pink-600:oklch(.592 .249 15.716);--color-pink-700:oklch(.525 .223 16.958);--color-pink-800:oklch(.459 .187 16.887);--color-pink-900:oklch(.408 .153 17.635);--color-pink-950:oklch(.284 .105 16.208);--color-rose-50:oklch(.961 .012 12.423);--color-rose-100:oklch(.926 .031 12.552);--color-rose-200:oklch(.869 .059 12.532);--color-rose-300:oklch(.79 .108 13.6);--color-rose-400:oklch(.712 .194 13.428);--color-rose-500:oklch(.645 .246 16.439);--color-rose-600:oklch(.586 .253 17.585);--color-rose-700:oklch(.514 .222 16.935);--color-rose-800:oklch(.45 .181 16.265);--color-rose-900:oklch(.397 .143 15.979);--color-rose-950:oklch(.258 .092 16.042);--color-slate-50:oklch(.984 .003 247.858);--color-slate-100:oklch(.968 .007 247.896);--color-slate-200:oklch(.929 .013 255.508);--color-slate-300:oklch(.869 .022 252.894);--color-slate-400:oklch(.704 .04 256.788);--color-slate-500:oklch(.546 .045 262.028);--color-slate-600:oklch(.446 .043 257.281);--color-slate-700:oklch(.372 .033 257.581);--color-slate-800:oklch(.279 .041 260.031);--color-slate-900:oklch(.208 .042 265.755);--color-slate-950:oklch(.129 .042 264.695);--color-gray-50:oklch(.985 .002 247.839);--color-gray-100:oklch(.967 .003 264.542);--color-gray-200:oklch(.928 .006 264.531);--color-gray-300:oklch(.872 .006 264.542);--color-gray-400:oklch(.707 .009 264.582);--color-gray-500:oklch(.551 .011 264.58);--color-gray-600:oklch(.446 .012 264.572);--color-gray-700:oklch(.373 .012 264.575);--color-gray-800:oklch(.278 .014 264.574);--color-gray-900:oklch(.21 .014 264.576);--color-gray-950:oklch(.13 .016 264.576);--color-zinc-50:oklch(.985 0 0);--color-zinc-100:oklch(.967 .001 286.375);--color-zinc-200:oklch(.92 .004 286.32);--color-zinc-300:oklch(.871 .006 286.286);--color-zinc-400:oklch(.705 .015 286.067);--color-zinc-500:oklch(.552 .016 285.938);--color-zinc-600:oklch(.442 .017 285.786);--color-zinc-700:oklch(.371 .013 285.805);--color-zinc-800:oklch(.274 .006 285.823);--color-zinc-900:oklch(.21 .006 285.885);--color-zinc-950:oklch(.141 .005 285.823);--color-neutral-50:oklch(.985 0 0);--color-neutral-100:oklch(.97 0 0);--color-neutral-200:oklch(.922 0 0);--color-neutral-300:oklch(.87 0 0);--color-neutral-400:oklch(.708 0 0);--color-neutral-500:oklch(.556 0 0);--color-neutral-600:oklch(.439 0 0);--color-neutral-700:oklch(.37 0 0);--color-neutral-800:oklch(.269 0 0);--color-neutral-900:oklch(.205 0 0);--color-neutral-950:oklch(.145 0 0);--color-stone-50:oklch(.985 .001 106.423);--color-stone-100:oklch(.97 .001 106.424);--color-stone-200:oklch(.923 .003 48.717);--color-stone-300:oklch(.869 .005 56.366);--color-stone-400:oklch(.708 .012 56.097);--color-stone-500:oklch(.553 .013 58.079);--color-stone-600:oklch(.444 .011 73.639);--color-stone-700:oklch(.369 .01 74.466);--color-stone-800:oklch(.276 .006 75.604);--color-stone-900:oklch(.21 .006 75.605);--color-stone-950:oklch(.144 .004 49.27);--color-black:#000;--color-white:#fff;--spacing:0.25rem;--breakpoint-sm:40rem;--breakpoint-md:48rem;--breakpoint-lg:64rem;--breakpoint-xl:80rem;--breakpoint-2xl:96rem;--container-3xs:16rem;--container-2xs:18rem;--container-xs:20rem;--container-ss:24rem;--container-sm:28rem;--container-md:32rem;--container-lg:36rem;--container-xl:42rem;--container-2xl:48rem;--container-3xl:56rem;--container-4xl:64rem;--container-5xl:72rem;--container-6xl:80rem;--container-7xl:88rem;--text-xs:0.75rem;--text-xs--line-height:calc(1 / .75);--text-sm:0.875rem;--text-sm--line-height:calc(1.25 / .875);--text-base:1rem;--text-base--line-height:calc(1.5 / 1);--text-lg:1.125rem;--text-lg--line-height:calc(1.75 / 1.125);--text-xl:1.25rem;--text-xl--line-height:calc(1.75 / 1.25);--text-2xl:1.5rem;--text-2xl--line-height:calc(2 / 1.5);--text-3xl:1.875rem;--text-3xl--line-height:calc(2.25 / 1.875);--text-4xl:2.25rem;--text-4xl--line-height:calc(2.5 / 2.25);--text-5xl:3rem;--text-5xl--line-height:1;--text-6xl:3.75rem;--text-6xl--line-height:1;--text-7xl:4.5rem;--text-7xl--line-height:1;--text-8xl:6rem;--text-8xl--line-height:1;--text-9xl:8rem;--text-9xl--line-height:1;--font-weight-thin:100;--font-weight-extralight:200;--font-weight-light:300;--font-weight-normal:400;--font-weight-medium:500;--font-weight-semibold:600;--font-weight-bold:700;--font-weight-extrabold:800;--font-weight-black:900;--tracking-tighter:-0.05em;--tracking-tight:-0.025em;--tracking-normal:0em;--tracking-wide:0.025em;--tracking-wider:0.05em;--tracking-widest:0.1em;--leading-tight:1.25;--leading-snug:1.375;--leading-normal:1.5;--leading-relaxed:1.625;--leading-loose:2;--radius-xs:0.125rem;--radius-sm:0.25rem;--radius-md:0.375rem;--radius-lg:0.5rem;--radius-xl:0.75rem;--radius-2xl:1rem;--radius-3xl:1.5rem;--radius-full:9999px;--shadow-2xs:0 1px rgb(0 0 0 / 0.05);--shadow-xs:0 1px 2px 0 rgb(0 0 0 / 0.05);--shadow-sm:0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);--shadow-md:0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);--shadow-lg:0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);--shadow-xl:0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);--shadow-2xl:0 25px 50px -12px rgb(0 0 0 / 0.25);--shadow-inner:inset 0 2px 4px 0 rgb(0 0 0 / 0.05);--blur-xs:4px;--blur-sm:8px;--blur-md:12px;--blur-lg:16px;--blur-xl:24px;--blur-2xl:40px;--blur-3xl:64px;--perspective-dramatic:100px;--perspective-near:300px;--perspective-normal:500px;--perspective-mid:800px;--perspective-distant:1200px;--aspect-video:16 / 9;--ease-in:cubic-bezier(0.4, 0, 1, 1);--ease-out:cubic-bezier(0, 0, 0.2, 1);--ease-in-out:cubic-bezier(0.4, 0, 0.2, 1);--animate-spin:spin 1s linear infinite;--animate-ping:ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;--animate-pulse:pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;--animate-bounce:bounce 1s infinite;@keyframes spin{to{transform:rotate(360deg)}}@keyframes ping{75%,100%{transform:scale(2);opacity:0}}@keyframes pulse{50%{opacity:0.5}}@keyframes bounce{0%,100%{transform:translateY(-25%);animation-timing-function:cubic-bezier(0.8, 0, 1, 1)}50%{transform:none;animation-timing-function:cubic-bezier(0, 0, 0.2, 1)}}}:root{--background:var(--color-white);--foreground:var(--color-zinc-950);--card:var(--color-white);--card-foreground:var(--color-zinc-950);--popover:var(--color-white);--popover-foreground:var(--color-zinc-950);--primary:var(--color-zinc-900);--primary-foreground:var(--color-zinc-50);--secondary:var(--color-zinc-100);--secondary-foreground:var(--color-zinc-900);--muted:var(--color-zinc-100);--muted-foreground:var(--color-zinc-500);--accent:var(--color-zinc-100);--accent-foreground:var(--color-zinc-900);--destructive:var(--color-red-500);--destructive-foreground:var(--color-zinc-50);--border:var(--color-zinc-200);--input:var(--color-zinc-200);--ring:var(--color-zinc-900);--radius:0.5rem;--chart-1:oklch(.646 .222 41.116);--chart-2:oklch(.6 .118 184.704);--chart-3:oklch(.398 .07 227.392);--chart-4:oklch(.828 .189 84.429);--chart-5:oklch(.769 .188 70.08)}}.dark{:root{--background:var(--color-zinc-950);--foreground:var(--color-zinc-50);--card:var(--color-zinc-950);--card-foreground:var(--color-zinc-50);--popover:var(--color-zinc-950);--popover-foreground:var(--color-zinc-50);--primary:var(--color-zinc-50);--primary-foreground:var(--color-zinc-900);--secondary:var(--color-zinc-800);--secondary-foreground:var(--color-zinc-50);--muted:var(--color-zinc-800);--muted-foreground:var(--color-zinc-400);--accent:var(--color-zinc-800);--accent-foreground:var(--color-zinc-50);--destructive:var(--color-red-900);--destructive-foreground:var(--color-zinc-50);--border:var(--color-zinc-800);--input:var(--color-zinc-800);--ring:var(--color-zinc-300);--chart-1:oklch(.488 .243 264.376);--chart-2:oklch(.696 .17 162.48);--chart-3:oklch(.769 .188 70.08);--chart-4:oklch(.627 .265 339.239);--chart-5:oklch(.645 .246 16.439)}}@layer base{*{border-color:var(--border);outline-color:var(--ring)}body{background-color:var(--background);color:var(--foreground)}}
            </style>
        @endif
    </head>
    <body class="bg-white text-[#1b1b18] flex flex-col min-h-screen">
        <header class="w-full bg-slate-900 backdrop-blur-md sticky top-0 z-50 text-white">
            <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Enumerate Logo" class="w-8 h-8 object-contain brightness-0 invert">
                    <span class="font-bold text-lg tracking-tight">Enumerate</span>
                </div>
                
                @if (Route::has('login'))
                    <nav class="flex items-center gap-4">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-sm font-medium hover:text-blue-400 transition-colors">Dashboard</a>
                        @else
                            <a href="{{ route('staff.login') }}" class="text-sm font-medium hover:text-blue-400 transition-colors">Log in</a>
                            @if (Route::has('staff.register'))
                                <a href="{{ route('staff.register') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-all shadow-sm">Get Started</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </div>
        </header>

        <main class="flex-grow">
            <!-- Hero Section -->
            <section class="relative py-24 lg:py-36 px-6 overflow-hidden bg-slate-900 text-white">
                <!-- Decorative Background Elements -->
                <div class="absolute top-0 right-0 w-1/2 h-full bg-blue-600/10 blur-[120px] pointer-events-none"></div>
                <div class="absolute -top-24 -left-24 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

                <div class="max-w-7xl mx-auto grid lg:grid-cols-2 gap-16 items-center relative z-10">
                    <div class="text-left">
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-white/5 text-blue-400 rounded-full text-xs font-bold mb-8 uppercase tracking-widest border border-white/10">
                            <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                            Next-Gen Enumeration Platform
                        </span>
                        <h1 class="text-5xl lg:text-8xl font-black mb-8 leading-[1.05] tracking-tight">
                            Collect Data <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Smarter</span>.
                        </h1>
                        <p class="text-xl text-slate-400 mb-12 leading-relaxed max-w-xl font-medium">
                            Enumerate by Cyber1 is a high-performance platform for modern field surveys. Transform complex data collection into a seamless digital experience.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-5">
                            <a href="{{ route('staff.login') }}" class="px-10 py-5 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:bg-blue-700 transition-all shadow-2xl shadow-blue-600/20 flex items-center justify-center gap-3 group">
                                Get Started Now
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                            <a href="#features" class="px-10 py-5 bg-white/5 border-2 border-white/10 rounded-2xl font-bold text-lg hover:bg-white/10 hover:border-blue-500 transition-all flex items-center justify-center">
                                Explore Features
                            </a>
                        </div>
                    </div>
                    <div class="relative">
                        <div class="relative bg-white/5 rounded-[3rem] p-4 shadow-2xl border border-white/10 transform lg:rotate-3 hover:rotate-0 transition-transform duration-500 backdrop-blur-sm">
                            <div class="bg-slate-900 rounded-[2.5rem] p-12 aspect-square flex items-center justify-center overflow-hidden relative group border border-white/5">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                <svg class="w-32 h-32 text-blue-500 transform group-hover:scale-110 transition-transform duration-500" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 12h6m-6 4h6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <!-- Floating Badge -->
                            <div class="absolute -bottom-6 -left-6 bg-slate-800 p-6 rounded-2xl shadow-xl border border-white/10 hidden md:block animate-bounce">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">System Status</p>
                                        <p class="text-sm font-black text-white">All Systems Online</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Stats/Info Section -->
            <section id="features" class="py-32 bg-white relative overflow-hidden">
                <div class="max-w-7xl mx-auto px-6 relative z-10">
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-20 gap-8">
                        <div class="max-w-2xl">
                            <h2 class="text-4xl lg:text-5xl font-black mb-6 text-slate-900 tracking-tight">The Complete <span class="text-blue-600">Lifecycle</span>.</h2>
                            <p class="text-lg text-slate-600 font-medium">From initial project design to field deployment and final analysis, we provide a unified experience for your entire team.</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="text-center px-8 py-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <p class="text-3xl font-black text-slate-900">100%</p>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Uptime</p>
                            </div>
                            <div class="text-center px-8 py-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <p class="text-3xl font-black text-slate-900">24/7</p>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Support</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-10">
                        <!-- Feature 1 -->
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-blue-600/30 group-hover:rotate-6 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <h3 class="text-2xl font-black mb-4 text-slate-900">Project Design</h3>
                            <p class="text-slate-600 leading-relaxed font-medium">Create custom enumeration projects with dynamic fields. Choose from text, numbers, dates, and secure file uploads.</p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-green-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-16 h-16 bg-green-500 text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-green-500/30 group-hover:rotate-6 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <h3 class="text-2xl font-black mb-4 text-slate-900">Mobile Ready</h3>
                            <p class="text-slate-600 leading-relaxed font-medium">Equip your staff with mobile-ready interfaces. Collect data in the field with ease and automatic organization.</p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-16 h-16 bg-purple-500 text-white rounded-2xl flex items-center justify-center mb-8 shadow-lg shadow-purple-500/30 group-hover:rotate-6 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            </div>
                            <h3 class="text-2xl font-black mb-4 text-slate-900">Live Analytics</h3>
                            <p class="text-slate-600 leading-relaxed font-medium">Monitor progress from your dashboard. View submissions as they happen and manage staff efficiently.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How It Works Section -->
            <section class="py-32 bg-slate-900 text-white relative overflow-hidden">
                <div class="absolute top-0 right-0 w-1/2 h-full bg-blue-600/10 blur-[120px]"></div>
                <div class="max-w-7xl mx-auto px-6 relative z-10">
                    <div class="grid lg:grid-cols-2 gap-24 items-center">
                        <div>
                            <h2 class="text-4xl lg:text-6xl font-black mb-12 tracking-tight leading-tight">Streamlined for <br/><span class="text-blue-500">Peak Performance</span></h2>
                            <div class="space-y-12">
                                <div class="flex gap-8 group">
                                    <div class="flex-shrink-0 w-14 h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center font-black text-xl border border-white/10 group-hover:bg-blue-600 group-hover:border-blue-600 transition-all">01</div>
                                    <div>
                                        <h4 class="text-2xl font-bold mb-3">Define Your Scope</h4>
                                        <p class="text-slate-400 text-lg leading-relaxed">Set up your project with custom fields tailored to your survey requirements. Define validations and mandatory data points.</p>
                                    </div>
                                </div>
                                <div class="flex gap-8 group">
                                    <div class="flex-shrink-0 w-14 h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center font-black text-xl border border-white/10 group-hover:bg-blue-600 group-hover:border-blue-600 transition-all">02</div>
                                    <div>
                                        <h4 class="text-2xl font-bold mb-3">Onboard Your Team</h4>
                                        <p class="text-slate-400 text-lg leading-relaxed">Quickly register and assign staff to projects. Secure authentication ensures only authorized personnel can collect data.</p>
                                    </div>
                                </div>
                                <div class="flex gap-8 group">
                                    <div class="flex-shrink-0 w-14 h-14 bg-white/10 text-white rounded-2xl flex items-center justify-center font-black text-xl border border-white/10 group-hover:bg-blue-600 group-hover:border-blue-600 transition-all">03</div>
                                    <div>
                                        <h4 class="text-2xl font-bold mb-3">Execute & Monitor</h4>
                                        <p class="text-slate-400 text-lg leading-relaxed">Field staff submit data via the mobile interface. Admin monitors real-time progress and manages project integrity.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-1 rounded-[3rem] shadow-2xl">
                                <div class="bg-slate-900 rounded-[2.8rem] p-16 aspect-square flex items-center justify-center text-center">
                                    <div>
                                        <div class="w-24 h-24 bg-blue-600/20 text-blue-500 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-blue-500/30">
                                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 21a9.003 9.003 0 008.367-5.618m-1.634-3.044A8.962 8.962 0 0012 12c-1.928 0-3.692.607-5.133 1.644M12 21c-4.474 0-8.064-3.59-8.064-8.064a8.06 8.06 0 011.644-4.867"/></svg>
                                        </div>
                                        <h3 class="text-3xl font-black mb-4">Secure Infrastructure</h3>
                                        <p class="text-slate-400 text-lg">Enterprise-grade encryption for every data point collected in the field.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Why Choose Us Section -->
            <section class="py-32 bg-white">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="text-center max-w-3xl mx-auto mb-24">
                        <h2 class="text-4xl lg:text-5xl font-black mb-6 text-slate-900 tracking-tight">Built for <span class="text-blue-600">Scale</span>.</h2>
                        <p class="text-lg text-slate-600 font-medium">Powerful features that grow with your project requirements.</p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-12">
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-blue-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-blue-600 mb-8 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 border border-slate-100">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 21a9.003 9.003 0 008.367-5.618m-1.634-3.044A8.962 8.962 0 0012 12c-1.928 0-3.692.607-5.133 1.644M12 21c-4.474 0-8.064-3.59-8.064-8.064a8.06 8.06 0 011.644-4.867"/></svg>
                            </div>
                            <h4 class="text-xl font-black mb-4 text-slate-900">Verified Data</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Built-in validation rules ensure data integrity from the moment of entry.</p>
                        </div>
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-green-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-green-600 mb-8 group-hover:bg-green-500 group-hover:text-white transition-all duration-500 border border-slate-100">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h4 class="text-xl font-black mb-4 text-slate-900">Instant Sync</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Real-time data synchronization between field staff and central servers.</p>
                        </div>
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-purple-600 mb-8 group-hover:bg-purple-500 group-hover:text-white transition-all duration-500 border border-slate-100">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <h4 class="text-xl font-black mb-4 text-slate-900">Collaboration</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Manage large teams with granular role-based access control.</p>
                        </div>
                        <div class="group p-10 bg-slate-50 rounded-[2.5rem] border border-slate-100 hover:bg-white hover:shadow-2xl hover:shadow-orange-500/10 hover:-translate-y-2 transition-all duration-500">
                            <div class="w-20 h-20 bg-slate-50 rounded-[2rem] flex items-center justify-center text-orange-600 mb-8 group-hover:bg-orange-500 group-hover:text-white transition-all duration-500 border border-slate-100">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <h4 class="text-xl font-black mb-4 text-slate-900">Rich Media</h4>
                            <p class="text-slate-600 font-medium leading-relaxed">Capture high-resolution photos and documents directly within forms.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="py-32 px-6">
                <div class="max-w-6xl mx-auto bg-slate-900 rounded-[4rem] p-16 lg:p-24 text-center text-white relative overflow-hidden shadow-3xl">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-blue-600/20 to-transparent"></div>
                    <div class="relative z-10">
                        <h2 class="text-4xl lg:text-7xl font-black mb-10 tracking-tight">Ready to <span class="text-blue-500">Transform?</span></h2>
                        <p class="text-xl text-slate-300 mb-14 max-w-2xl mx-auto font-medium">Join leading organizations using Enumerate to modernize their field operations.</p>
                        <div class="flex flex-col sm:flex-row gap-6 justify-center">
                            <a href="{{ route('staff.register') }}" class="px-12 py-5 bg-blue-600 text-white rounded-2xl font-black text-lg hover:bg-blue-700 transition-all shadow-xl shadow-blue-600/30">Create Account</a>
                            <a href="{{ route('staff.login') }}" class="px-12 py-5 bg-white/10 text-white rounded-2xl font-black text-lg hover:bg-white/20 transition-all border border-white/10">Staff Login</a>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="py-12 px-6 border-t border-white/10 bg-slate-900 text-white">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Enumerate Logo" class="w-6 h-6 object-contain brightness-0 invert opacity-80">
                    <span class="font-bold text-sm tracking-tight text-slate-200">Enumerate by Cyber1</span>
                </div>
                <p class="text-sm text-slate-400">&copy; {{ date('Y') }} Cyber1 Systems Network. All rights reserved.</p>
                <div class="flex gap-6">
                    <a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors">Privacy Policy</a>
                    <a href="#" class="text-sm text-slate-400 hover:text-blue-400 transition-colors">Terms of Service</a>
                </div>
            </div>
        </footer>
    </body>
</html>