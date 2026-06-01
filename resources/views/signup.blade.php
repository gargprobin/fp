@extends('layouts.app')

@section('title', 'Sign Up - ' . ($siteSettings->platform_name ?? 'Sourcing Portal'))

@section('content')
<div class="max-w-2xl mx-auto my-8 bg-[var(--theme-card)] rounded-2xl p-8 shadow-xl border border-[var(--theme-border)] transition-all duration-300" x-data="{ role: 'candidate' }">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-theme-primary to-theme-accent bg-clip-text text-transparent mb-2">
            Create Your Account
        </h1>
        <p class="text-sm text-theme-text/70">
            Join our premium recruitment ecosystem to discover teaching roles or hire top-tier faculty.
        </p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 text-red-500 border border-red-500/20 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('signup.submit') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Role Selector Toggle -->
        <div>
            <label class="block text-xs font-bold text-theme-text/60 uppercase mb-2">Select Your Role</label>
            <div class="grid grid-cols-2 gap-4">
                <button type="button" @click="role = 'candidate'" :class="role === 'candidate' ? 'bg-theme-primary/10 border-theme-primary text-theme-primary' : 'bg-transparent border-[var(--theme-border)] text-theme-text/60'" class="py-3.5 rounded-xl border-2 font-bold text-sm transition duration-300 flex items-center justify-center gap-2">
                    🧑‍🏫 Faculty Candidate
                </button>
                <button type="button" @click="role = 'institution'" :class="role === 'institution' ? 'bg-theme-accent/10 border-theme-accent text-theme-accent' : 'bg-transparent border-[var(--theme-border)] text-theme-text/60'" class="py-3.5 rounded-xl border-2 font-bold text-sm transition duration-300 flex items-center justify-center gap-2">
                    🏢 Recruiter / Institution
                </button>
            </div>
            <input type="hidden" name="role" :value="role" />
        </div>

        <!-- Base User Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Full Name *</label>
                <input type="text" name="name" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. Dr. Amit Sharma" value="{{ old('name') }}" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Email Address *</label>
                <input type="email" name="email" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. sharma@gmail.com" value="{{ old('email') }}" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Password *</label>
                <input type="password" name="password" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="••••••••" />
            </div>
            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Confirm Password *</label>
                <input type="password" name="password_confirmation" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="••••••••" />
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Contact Number / Phone *</label>
            <input type="text" name="phone" required class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. 9876543210" value="{{ old('phone') }}" />
        </div>

        <!-- Candidate Dynamic Fields -->
        <div x-show="role === 'candidate'" class="space-y-6 pt-4 border-t border-[var(--theme-border)]" x-cloak>
            <h3 class="text-sm font-black text-theme-primary uppercase tracking-wider">🎓 Faculty Profile Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Current Designation *</label>
                    <input type="text" name="current_designation" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. Senior Physics Faculty" value="{{ old('current_designation') }}" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Highest Degree *</label>
                    <input type="text" name="highest_degree" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. M.Tech from IIT Delhi" value="{{ old('highest_degree') }}" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Subjects Taught (Comma Separated) *</label>
                    <input type="text" name="subjects_taught" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. Physics, Mechanics" value="{{ old('subjects_taught') }}" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Years of Experience *</label>
                    <input type="number" step="0.1" name="experience_years" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. 6.5" value="{{ old('experience_years') }}" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Current CTC (INR / Annum) *</label>
                    <input type="number" name="current_ctc" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. 1200000" value="{{ old('current_ctc') }}" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Expected CTC (INR / Annum) *</label>
                    <input type="number" name="expected_ctc" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. 1800000" value="{{ old('expected_ctc') }}" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Preferred Location *</label>
                <input type="text" name="preferred_location" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. Kota, Delhi or Bangalore" value="{{ old('preferred_location') }}" />
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Brief Experience Summary *</label>
                <textarea name="experience_summary" rows="3" x-bind:required="role === 'candidate'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="Summarize your teaching methodologies and results...">{{ old('experience_summary') }}</textarea>
            </div>
        </div>

        <!-- Recruiter Dynamic Fields -->
        <div x-show="role === 'institution'" class="space-y-6 pt-4 border-t border-[var(--theme-border)]" x-cloak>
            <h3 class="text-sm font-black text-theme-accent uppercase tracking-wider">🏢 Institution/Coaching Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Company/Coaching Name *</label>
                    <input type="text" name="company_name" x-bind:required="role === 'institution'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. Vidyapeeth Classes" value="{{ old('company_name') }}" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Institution Type *</label>
                    <select name="type" x-bind:required="role === 'institution'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text">
                        <option value="coaching_center">Coaching Center</option>
                        <option value="school">School / Academy</option>
                        <option value="university">University</option>
                        <option value="other">Other Educational Org</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Official Website *</label>
                    <input type="url" name="website" x-bind:required="role === 'institution'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="https://vidyapeeth.in" value="{{ old('website') }}" />
                </div>
                <div>
                    <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Location (City, State) *</label>
                    <input type="text" name="location" x-bind:required="role === 'institution'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="e.g. Kota, Rajasthan" value="{{ old('location') }}" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-theme-text/60 uppercase mb-1.5">Brief Organization Description *</label>
                <textarea name="description" rows="3" x-bind:required="role === 'institution'" class="w-full p-3 text-sm bg-[var(--theme-bg)] border border-[var(--theme-border)] rounded-xl focus:outline-none focus:ring-2 focus:ring-theme-primary text-theme-text" placeholder="Tell candidates about your institute, campus size, and student strength...">{{ old('description') }}</textarea>
            </div>
        </div>

        <button type="submit" class="w-full py-4 text-white font-extrabold rounded-xl shadow-lg transition transform active:scale-[0.98] text-sm bg-theme-primary hover:bg-theme-accent">
            Complete Registration & Sign In &rarr;
        </button>
    </form>

    <div class="text-center mt-6 text-sm text-theme-text/60">
        Already have an account? 
        <a href="{{ route('login-demo') }}" class="text-theme-primary font-bold hover:underline">Log In Here</a>
    </div>
</div>
@endsection
