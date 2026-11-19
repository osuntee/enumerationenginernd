<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Enumerate') }} — Field data collection</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter-tight:400,500,600,700,800|jetbrains-mono:400,500" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body { font-family: 'Inter Tight', ui-sans-serif, system-ui, sans-serif; background: #0b1220; color: #e2e8f0; }
        .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }

        /* Backdrop grid + glow */
        .bg-grid {
            background-image:
                linear-gradient(to right, rgba(148,163,184,.08) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(148,163,184,.08) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 70% 60% at 50% 0%, #000 40%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 70% 60% at 50% 0%, #000 40%, transparent 100%);
        }
        .glow-blue { background: radial-gradient(closest-side, rgba(37,99,235,.35), transparent); }
        .glow-indigo { background: radial-gradient(closest-side, rgba(79,70,229,.28), transparent); }

        /* Surfaces */
        .panel { background: linear-gradient(180deg, rgba(30,41,59,.65), rgba(15,23,42,.65)); border: 1px solid rgba(148,163,184,.14); }
        .panel-light { background: #f8fafc; color: #0f172a; border: 1px solid #e2e8f0; }
        .hairline { border-color: rgba(148,163,184,.14); }

        /* Marquee */
        .marquee { display: flex; width: max-content; animation: scroll 38s linear infinite; }
        .marquee:hover { animation-play-state: paused; }
        @keyframes scroll { to { transform: translateX(-50%); } }

        /* Reveal on scroll */
        .reveal { opacity: 0; transform: translateY(18px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.in { opacity: 1; transform: none; }

        /* Buttons */
        .btn-blue { background: #2563eb; color: #fff; box-shadow: 0 0 0 1px rgba(255,255,255,.12) inset, 0 10px 30px -8px rgba(37,99,235,.6); transition: background .2s, transform .2s; }
        .btn-blue:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-outline { border: 1px solid rgba(148,163,184,.3); transition: border-color .2s, background .2s; }
        .btn-outline:hover { border-color: #3b82f6; background: rgba(59,130,246,.08); }

        /* FAQ */
        details > summary { list-style: none; cursor: pointer; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .plus { transform: rotate(45deg); }
        .plus { transition: transform .2s; }

        /* Cursor blink + live dot */
        .live-dot { position: relative; }
        .live-dot::after { content:""; position:absolute; inset:-4px; border-radius:9999px; border:1px solid #22c55e; opacity:.6; animation: ring 2s ease-out infinite; }
        @keyframes ring { from { transform: scale(.6); opacity: .8; } to { transform: scale(1.6); opacity: 0; } }

        @media (prefers-reduced-motion: reduce) {
            .marquee, .live-dot::after { animation: none; }
            .reveal { opacity: 1; transform: none; transition: none; }
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col">

    {{-- ============ NAV ============ --}}
    <header class="sticky top-0 z-50 border-b hairline backdrop-blur-xl" style="background: rgba(11,18,32,.72)">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.png') }}" alt="Enumerate logo" class="w-7 h-7 object-contain brightness-0 invert">
                <span class="font-bold text-lg tracking-tight">Enumerate</span>
            </a>

            <nav class="hidden md:flex items-center gap-8 text-sm text-slate-400">
                <a href="#features" class="hover:text-white transition-colors">Features</a>
                <a href="#workflow" class="hover:text-white transition-colors">Workflow</a>
                <a href="#roles" class="hover:text-white transition-colors">Roles</a>
                <a href="#faq" class="hover:text-white transition-colors">FAQ</a>
            </nav>

            @if (Route::has('login'))
                <div class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-blue px-4 py-2 rounded-lg font-medium">Dashboard</a>
                    @else
                        <a href="{{ route('staff.login') }}" class="text-slate-300 hover:text-white transition-colors px-2">Log in</a>
                        @if (Route::has('staff.register'))
                            <a href="{{ route('staff.register') }}" class="btn-blue px-4 py-2 rounded-lg font-medium">Get started</a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </header>

    <main class="flex-grow">

        {{-- ============ HERO ============ --}}
        <section class="relative overflow-hidden">
            <div class="absolute inset-0 bg-grid pointer-events-none"></div>
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[900px] h-[600px] glow-blue pointer-events-none"></div>
            <div class="absolute top-40 -right-40 w-[600px] h-[600px] glow-indigo pointer-events-none"></div>

            <div class="relative max-w-7xl mx-auto px-6 pt-20 pb-12 lg:pt-28">
                <div class="max-w-4xl mx-auto text-center">
                    <a href="#workflow" class="inline-flex items-center gap-2 pl-2 pr-4 py-1.5 rounded-full text-xs panel hover:border-blue-500/50 transition-colors">
                        <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white font-semibold">New</span>
                        <span class="text-slate-300">Dynamic project forms with built-in validation</span>
                        <span class="text-slate-500">→</span>
                    </a>

                    <h1 class="mt-8 text-5xl sm:text-6xl lg:text-[5.5rem] font-extrabold tracking-[-0.04em] leading-[0.98] text-white">
                        Field data, collected<br class="hidden sm:block">
                        <span class="text-blue-400">the way it should be.</span>
                    </h1>

                    <p class="mt-8 text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed">
                        Enumerate by Cyber1 turns paper-based surveys into a single digital workflow: design the form, equip your team, and watch clean submissions arrive in real time.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="{{ route('staff.login') }}" class="btn-blue px-8 py-4 rounded-xl font-semibold text-base inline-flex items-center justify-center gap-2 group">
                            Get started now
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="#features" class="btn-outline px-8 py-4 rounded-xl font-semibold text-base text-slate-200 inline-flex items-center justify-center">Explore features</a>
                    </div>
                </div>

                {{-- Product window --}}
                <div class="relative mt-20 max-w-6xl mx-auto reveal">
                    <div class="absolute -inset-px rounded-3xl bg-gradient-to-b from-blue-500/40 via-indigo-500/10 to-transparent blur-sm"></div>
                    <div class="relative rounded-3xl panel overflow-hidden shadow-2xl shadow-blue-950/50">
                        {{-- window chrome --}}
                        <div class="flex items-center gap-2 px-5 h-11 border-b hairline">
                            <span class="w-3 h-3 rounded-full bg-slate-600/60"></span>
                            <span class="w-3 h-3 rounded-full bg-slate-600/60"></span>
                            <span class="w-3 h-3 rounded-full bg-slate-600/60"></span>
                            <span class="ml-4 mono text-xs text-slate-500">enumerate / projects / household-survey</span>
                        </div>

                        <div class="grid lg:grid-cols-12">
                            {{-- sidebar --}}
                            <aside class="hidden lg:block lg:col-span-2 border-r hairline p-5 text-sm space-y-1 text-slate-400">
                                <p class="mono text-[10px] uppercase tracking-widest text-slate-600 mb-3">Workspace</p>
                                <p class="px-3 py-2 rounded-lg bg-blue-600/15 text-blue-300">Overview</p>
                                <p class="px-3 py-2">Projects</p>
                                <p class="px-3 py-2">Submissions</p>
                                <p class="px-3 py-2">Staff</p>
                                <p class="px-3 py-2">Settings</p>
                            </aside>

                            {{-- main --}}
                            <div class="lg:col-span-10 p-6 sm:p-8">
                                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                                    <div>
                                        <p class="mono text-[11px] uppercase tracking-widest text-slate-500">Project</p>
                                        <h3 class="text-2xl font-bold text-white">Household Survey</h3>
                                    </div>
                                    <span class="inline-flex items-center gap-2 text-xs text-slate-300 px-3 py-1.5 rounded-full border hairline">
                                        <span class="live-dot w-2 h-2 rounded-full bg-green-500"></span> Live collection
                                    </span>
                                </div>

                                <div class="grid sm:grid-cols-3 gap-4">
                                    @foreach ([['Submissions', '4,812', 'text-white'], ['Active staff', '38', 'text-white'], ['Flagged for review', '23', 'text-orange-400']] as [$l, $v, $c])
                                        <div class="rounded-2xl border hairline p-5 bg-slate-900/50">
                                            <p class="text-xs text-slate-500">{{ $l }}</p>
                                            <p class="mt-2 text-3xl font-bold tracking-tight {{ $c }}">{{ $v }}</p>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 grid lg:grid-cols-5 gap-4">
                                    {{-- bars --}}
                                    <div class="lg:col-span-3 rounded-2xl border hairline p-5 bg-slate-900/50">
                                        <div class="flex items-center justify-between mb-5">
                                            <p class="text-sm font-medium text-slate-300">Submissions per day</p>
                                            <p class="mono text-[11px] text-slate-500">last 14 days</p>
                                        </div>
                                        <div class="flex items-end gap-2 h-32">
                                            @foreach ([28, 42, 38, 55, 61, 48, 70, 66, 82, 74, 90, 85, 96, 100] as $h)
                                                <div class="flex-1 rounded-t-md {{ $loop->last ? 'bg-blue-500' : 'bg-blue-500/30' }}" style="height: {{ $h }}%"></div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- feed --}}
                                    <div class="lg:col-span-2 rounded-2xl border hairline p-5 bg-slate-900/50">
                                        <p class="text-sm font-medium text-slate-300 mb-4">Latest submissions</p>
                                        <ul class="space-y-3 text-sm">
                                            @foreach ([['#004817', 'Verified', 'text-green-400'], ['#004816', 'Verified', 'text-green-400'], ['#004815', 'Needs review', 'text-orange-400'], ['#004814', 'Verified', 'text-green-400']] as [$id, $s, $c])
                                                <li class="flex items-center justify-between">
                                                    <span class="mono text-slate-400">{{ $id }}</span>
                                                    <span class="{{ $c }} text-xs">{{ $s }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ MARQUEE: field types ============ --}}
        <section class="border-y hairline py-6 overflow-hidden" aria-label="Supported field types">
            <div class="marquee mono text-sm text-slate-500 uppercase tracking-widest">
                @for ($i = 0; $i < 2; $i++)
                    @foreach (['Text', 'Numbers', 'Dates', 'Dropdowns', 'Checkboxes', 'Photo capture', 'File upload', 'Validation rules', 'Required fields', 'Role access'] as $t)
                        <span class="flex items-center gap-10 pr-10 whitespace-nowrap">{{ $t }} <span class="text-blue-500">/</span></span>
                    @endforeach
                @endfor
            </div>
        </section>

        {{-- ============ BENTO FEATURES ============ --}}
        <section id="features" class="relative py-28">
            <div class="max-w-7xl mx-auto px-6">
                <div class="max-w-2xl reveal">
                    <p class="mono text-xs uppercase tracking-[0.2em] text-blue-400 mb-4">The complete lifecycle</p>
                    <h2 class="text-4xl lg:text-5xl font-extrabold tracking-[-0.03em] text-white leading-[1.05]">One platform from form design to final analysis.</h2>
                    <p class="mt-5 text-lg text-slate-400">Everything your team needs for a field survey, without stitching together five different tools.</p>
                </div>

                <div class="mt-16 grid md:grid-cols-6 gap-5">
                    {{-- Large: project design --}}
                    <div class="reveal md:col-span-4 panel rounded-3xl p-8 lg:p-10 relative overflow-hidden group hover:border-blue-500/40 transition-colors">
                        <div class="absolute -right-24 -top-24 w-72 h-72 glow-blue opacity-60 group-hover:opacity-100 transition-opacity"></div>
                        <p class="mono text-xs text-blue-400 mb-3">01 — Design</p>
                        <h3 class="text-2xl lg:text-3xl font-bold text-white tracking-tight">Project design with dynamic fields</h3>
                        <p class="mt-3 text-slate-400 max-w-md leading-relaxed">Build custom enumeration projects in minutes. Choose from text, numbers, dates and secure file uploads, then decide which answers are mandatory.</p>

                        <div class="mt-8 grid sm:grid-cols-2 gap-3 max-w-lg">
                            @foreach (['Full name · Text', 'Household size · Number', 'Visit date · Date', 'ID document · File'] as $f)
                                <div class="flex items-center justify-between rounded-xl border hairline bg-slate-900/60 px-4 py-3 text-sm text-slate-300">
                                    <span>{{ $f }}</span>
                                    <span class="text-slate-600">⋮⋮</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Mobile --}}
                    <div class="reveal md:col-span-2 rounded-3xl p-8 bg-blue-600 text-white relative overflow-hidden">
                        <div class="absolute -bottom-10 -right-10 w-48 h-48 rounded-full bg-white/10"></div>
                        <p class="mono text-xs text-blue-200 mb-3">02 — Collect</p>
                        <h3 class="text-2xl font-bold tracking-tight">Mobile ready</h3>
                        <p class="mt-3 text-blue-100 leading-relaxed">Staff capture data on their phones with a clean interface and automatic organization.</p>
                        <svg class="relative mt-8 w-14 h-14 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>

                    {{-- Analytics --}}
                    <div class="reveal md:col-span-2 panel rounded-3xl p-8 hover:border-green-500/40 transition-colors">
                        <p class="mono text-xs text-green-400 mb-3">03 — Monitor</p>
                        <h3 class="text-2xl font-bold text-white tracking-tight">Live analytics</h3>
                        <p class="mt-3 text-slate-400 leading-relaxed">See submissions as they happen and manage staff from one dashboard.</p>
                        <div class="mt-6 flex items-end gap-1.5 h-14">
                            @foreach ([30, 50, 40, 70, 55, 85, 100] as $h)
                                <div class="flex-1 rounded-sm bg-green-500/{{ $loop->last ? '90' : '35' }}" style="height: {{ $h }}%"></div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Validation --}}
                    <div class="reveal md:col-span-2 panel rounded-3xl p-8 hover:border-purple-500/40 transition-colors">
                        <p class="mono text-xs text-purple-400 mb-3">04 — Verify</p>
                        <h3 class="text-2xl font-bold text-white tracking-tight">Verified data</h3>
                        <p class="mt-3 text-slate-400 leading-relaxed">Validation rules catch bad entries the moment they are typed.</p>
                        <div class="mt-6 mono text-xs rounded-lg bg-slate-900/70 border hairline p-3 text-slate-400">
                            phone: <span class="text-orange-400">"0803"</span> → <span class="text-red-400">invalid</span>
                        </div>
                    </div>

                    {{-- Sync --}}
                    <div class="reveal md:col-span-2 panel rounded-3xl p-8 hover:border-orange-500/40 transition-colors">
                        <p class="mono text-xs text-orange-400 mb-3">05 — Sync</p>
                        <h3 class="text-2xl font-bold text-white tracking-tight">Central server</h3>
                        <p class="mt-3 text-slate-400 leading-relaxed">Field submissions flow to one place, ready for your team to review.</p>
                        <svg class="mt-6 w-12 h-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ WORKFLOW (sticky) ============ --}}
        <section id="workflow" class="relative border-t hairline py-28">
            <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-12 gap-16">
                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-28">
                        <p class="mono text-xs uppercase tracking-[0.2em] text-blue-400 mb-4">Workflow</p>
                        <h2 class="text-4xl lg:text-5xl font-extrabold tracking-[-0.03em] text-white leading-[1.05]">Streamlined for <span class="text-blue-400">peak performance.</span></h2>
                        <p class="mt-5 text-lg text-slate-400 leading-relaxed">Three steps from a blank project to a monitored field operation.</p>
                        <a href="{{ route('staff.login') }}" class="btn-blue mt-8 inline-flex px-6 py-3 rounded-xl font-semibold">Start a project</a>
                    </div>
                </div>

                <div class="lg:col-span-7 space-y-5">
                    @foreach ([
                        ['01', 'Define your scope', 'Set up your project with custom fields tailored to your survey. Define validations and mandatory data points before anyone steps into the field.', 'blue'],
                        ['02', 'Onboard your team', 'Quickly register and assign staff to projects. Secure authentication means only authorized personnel can collect data.', 'indigo'],
                        ['03', 'Execute and monitor', 'Field staff submit data through the mobile interface while admins follow real-time progress and keep the project on track.', 'green'],
                    ] as [$n, $t, $d, $c])
                        <div class="reveal panel rounded-3xl p-8 lg:p-10 flex gap-6 sm:gap-8">
                            <span class="mono text-5xl lg:text-6xl font-medium text-{{ $c }}-500/40 leading-none">{{ $n }}</span>
                            <div>
                                <h3 class="text-2xl font-bold text-white tracking-tight">{{ $t }}</h3>
                                <p class="mt-3 text-slate-400 leading-relaxed">{{ $d }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ ROLES (light contrast section) ============ --}}
        <section id="roles" class="py-28 bg-slate-50 text-slate-900">
            <div class="max-w-7xl mx-auto px-6">
                <div class="max-w-2xl reveal">
                    <p class="mono text-xs uppercase tracking-[0.2em] text-blue-600 mb-4">Built for teams</p>
                    <h2 class="text-4xl lg:text-5xl font-extrabold tracking-[-0.03em] leading-[1.05]">The right access for every person on the project.</h2>
                    <p class="mt-5 text-lg text-slate-600">Granular role-based access keeps large teams organized and sensitive data in the right hands.</p>
                </div>

                <div class="mt-16 grid md:grid-cols-3 gap-6">
                    @foreach ([
                        ['Administrators', 'Create projects, manage staff and oversee every submission.', ['Design project forms', 'Register and assign staff', 'Review all submissions'], 'blue'],
                        ['Supervisors', 'Keep field teams on track and check data quality as it comes in.', ['Monitor team progress', 'Review flagged records', 'Follow live submissions'], 'purple'],
                        ['Field staff', 'Collect data quickly with a focused interface on their own device.', ['Submit records on mobile', 'Attach photos and files', 'Sign in securely'], 'green'],
                    ] as [$role, $desc, $items, $c])
                        <div class="reveal rounded-3xl bg-white border border-slate-200 p-8 hover:shadow-xl hover:shadow-slate-200 hover:-translate-y-1 transition-all duration-300">
                            <span class="inline-block w-2.5 h-2.5 rounded-full bg-{{ $c }}-500"></span>
                            <h3 class="mt-5 text-2xl font-bold tracking-tight">{{ $role }}</h3>
                            <p class="mt-2 text-slate-600 leading-relaxed">{{ $desc }}</p>
                            <ul class="mt-6 space-y-3 text-sm text-slate-700">
                                @foreach ($items as $item)
                                    <li class="flex items-center gap-3">
                                        <svg class="w-4 h-4 text-{{ $c }}-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        {{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ RICH MEDIA + SECURITY split ============ --}}
        <section class="py-28">
            <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-6">
                <div class="reveal panel rounded-3xl p-10 lg:p-12 relative overflow-hidden">
                    <div class="absolute -left-20 -bottom-20 w-80 h-80 glow-indigo"></div>
                    <p class="mono text-xs text-indigo-400 mb-3">Rich media</p>
                    <h3 class="relative text-3xl font-bold text-white tracking-tight">Capture photos and documents inside the form.</h3>
                    <p class="relative mt-4 text-slate-400 leading-relaxed max-w-md">No separate gallery uploads or loose files. Evidence stays attached to the record it belongs to.</p>
                    <div class="relative mt-10 grid grid-cols-3 gap-3 max-w-sm">
                        @foreach (range(1, 3) as $i)
                            <div class="aspect-square rounded-xl border hairline bg-slate-900/70 flex items-center justify-center text-slate-600">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="reveal rounded-3xl p-10 lg:p-12 relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700">
                    <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10"></div>
                    <p class="mono text-xs text-blue-200 mb-3">Secure access</p>
                    <h3 class="relative text-3xl font-bold text-white tracking-tight">Only authorized staff collect data.</h3>
                    <p class="relative mt-4 text-blue-100 leading-relaxed max-w-md">Every enumerator signs in with their own account, so each record can be traced back to who collected it.</p>
                    <svg class="relative mt-10 w-16 h-16 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04M12 21a9.003 9.003 0 008.367-5.618m-1.634-3.044A8.962 8.962 0 0012 12c-1.928 0-3.692.607-5.133 1.644M12 21c-4.474 0-8.064-3.59-8.064-8.064a8.06 8.06 0 011.644-4.867"/></svg>
                </div>
            </div>
        </section>

        {{-- ============ FAQ ============ --}}
        <section id="faq" class="border-t hairline py-28">
            <div class="max-w-4xl mx-auto px-6">
                <div class="text-center reveal">
                    <p class="mono text-xs uppercase tracking-[0.2em] text-blue-400 mb-4">FAQ</p>
                    <h2 class="text-4xl lg:text-5xl font-extrabold tracking-[-0.03em] text-white">Common questions</h2>
                </div>

                <div class="mt-14 divide-y hairline border-y hairline">
                    @foreach ([
                        ['What kinds of data can I collect?', 'Text, numbers, dates and file uploads, including photos and documents. Fields can be marked as mandatory and given validation rules.'],
                        ['Can my field staff use it on their phones?', 'Yes. The interface is built to be used on mobile devices, so enumerators can submit records from the field.'],
                        ['How do I add my team?', 'Register staff accounts and assign them to projects. Role-based access controls what each person can see and do.'],
                        ['Can I follow progress while the survey is running?', 'Yes. The dashboard shows submissions as they arrive, so you can monitor progress and manage staff without waiting for a final upload.'],
                    ] as [$q, $a])
                        <details class="group py-6 reveal">
                            <summary class="flex items-center justify-between gap-6">
                                <span class="text-lg font-semibold text-white">{{ $q }}</span>
                                <span class="plus text-2xl text-blue-400 leading-none">+</span>
                            </summary>
                            <p class="mt-4 text-slate-400 leading-relaxed max-w-3xl">{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ CTA ============ --}}
        <section class="px-6 pb-28">
            <div class="relative max-w-6xl mx-auto rounded-[2rem] overflow-hidden panel p-12 lg:p-20 text-center reveal">
                <div class="absolute inset-0 bg-grid opacity-70 pointer-events-none"></div>
                <div class="absolute left-1/2 -top-40 -translate-x-1/2 w-[700px] h-[400px] glow-blue pointer-events-none"></div>
                <div class="relative">
                    <h2 class="text-4xl lg:text-6xl font-extrabold tracking-[-0.03em] text-white leading-[1.02]">Ready to <span class="text-blue-400">transform</span><br class="hidden sm:block"> your field operations?</h2>
                    <p class="mt-6 text-lg text-slate-400 max-w-xl mx-auto">Join the organizations using Enumerate to modernize how they collect data.</p>
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        @if (Route::has('staff.register'))
                            <a href="{{ route('staff.register') }}" class="btn-blue px-8 py-4 rounded-xl font-semibold">Create account</a>
                        @endif
                        <a href="{{ route('staff.login') }}" class="btn-outline px-8 py-4 rounded-xl font-semibold text-slate-200">Staff login</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    {{-- ============ FOOTER ============ --}}
    <footer class="border-t hairline">
        <div class="max-w-7xl mx-auto px-6 py-14 grid md:grid-cols-12 gap-10 text-sm">
            <div class="md:col-span-5">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.png') }}" alt="" class="w-6 h-6 object-contain brightness-0 invert opacity-80">
                    <span class="font-bold text-slate-200 text-base">Enumerate</span>
                </div>
                <p class="mt-4 text-slate-500 max-w-sm leading-relaxed">A field data collection platform by Cyber1 Systems Network.</p>
            </div>
            <div class="md:col-span-3">
                <p class="mono text-[11px] uppercase tracking-widest text-slate-600 mb-4">Product</p>
                <ul class="space-y-3 text-slate-400">
                    <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                    <li><a href="#workflow" class="hover:text-white transition-colors">Workflow</a></li>
                    <li><a href="#roles" class="hover:text-white transition-colors">Roles</a></li>
                    <li><a href="#faq" class="hover:text-white transition-colors">FAQ</a></li>
                </ul>
            </div>
            <div class="md:col-span-4">
                <p class="mono text-[11px] uppercase tracking-widest text-slate-600 mb-4">Legal</p>
                <ul class="space-y-3 text-slate-400">
                    <li><a href="#" class="hover:text-white transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t hairline">
            <p class="max-w-7xl mx-auto px-6 py-6 text-xs text-slate-500">&copy; {{ date('Y') }} Cyber1 Systems Network. All rights reserved.</p>
        </div>
    </footer>

    <script>
        (function () {
            var els = document.querySelectorAll('.reveal');
            if (!('IntersectionObserver' in window)) { els.forEach(function (e) { e.classList.add('in'); }); return; }
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (en) {
                    if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
                });
            }, { threshold: 0.12 });
            els.forEach(function (e) { io.observe(e); });
        })();
    </script>
</body>
</html>