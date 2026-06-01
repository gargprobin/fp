<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired / Security Token Refresh</title>
    <!-- Modern Typography from Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN for rich aesthetics -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #020617 100%);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 text-slate-100 overflow-hidden relative">

    <!-- Glowing Background blobs for premium glassmorphism -->
    <div class="absolute w-[40rem] h-[40rem] bg-indigo-500/10 rounded-full blur-3xl -top-40 -left-40 animate-pulse"></div>
    <div class="absolute w-[30rem] h-[30rem] bg-pink-500/10 rounded-full blur-3xl -bottom-20 -right-20 animate-pulse" style="animation-delay: 2s;"></div>

    <div class="max-w-lg w-full bg-slate-900/60 backdrop-blur-xl border border-white/10 p-10 rounded-3xl shadow-2xl text-center space-y-8 relative z-10 transform hover:scale-[1.01] transition-transform duration-300">
        
        <!-- Animated Security/Clock Icon -->
        <div class="flex justify-center">
            <div class="relative">
                <div class="absolute inset-0 bg-amber-500/20 rounded-full blur-xl animate-ping"></div>
                <div class="w-20 h-20 bg-gradient-to-tr from-amber-500 to-orange-600 rounded-full flex items-center justify-center shadow-lg relative border border-white/20">
                    <svg class="w-10 h-10 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Dynamic Header -->
        <div class="space-y-3">
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-amber-200 via-orange-300 to-amber-100 bg-clip-text text-transparent">
                सत्र समाप्त / Session Expired
            </h1>
            <p class="text-slate-400 text-sm leading-relaxed">
                सुरक्षा कारणों से आपका लॉग-इन सत्र (Security Token) समाप्त हो गया है। ऐसा तब होता है जब पेज काफ़ी देर तक खुला रहे या सिस्टम कैश साफ़ किया गया हो।
            </p>
            <p class="text-xs text-slate-500 italic">
                To protect your sensitive data, Laravel automatically expires page forms after inactivity.
            </p>
        </div>

        <!-- Informative visual box -->
        <div class="p-4 bg-slate-800/40 rounded-2xl border border-white/5 text-left text-xs space-y-2">
            <div class="flex items-center gap-2 text-amber-400 font-bold">
                <span>🛡️ Mutual Privacy Protection Active:</span>
            </div>
            <p class="text-slate-400 leading-normal">
                चिंता न करें! आपका कोई भी डेटा नष्ट नहीं हुआ है। बस नीचे दिए गए बटन पर क्लिक करें, पेज स्वतः ही री-लोड हो जाएगा और आप पुनः सुरक्षित रूप से कार्य जारी रख सकेंगे।
            </p>
        </div>

        <!-- Premium Interactive Refresh Button -->
        <button onclick="refreshReferrer()" class="w-full py-4 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 active:scale-[0.98] text-white font-bold rounded-2xl shadow-lg hover:shadow-orange-500/20 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer border border-white/10 group">
            <svg class="w-5 h-5 group-hover:rotate-180 transition-transform duration-500 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.27 15H18"></path>
            </svg>
            पेज ताज़ा करें (Refresh & Continue)
        </button>

    </div>

    <!-- Referrer Reload and redirect script -->
    <script>
        function refreshReferrer() {
            // Safely redirect back to referrer page and force-refresh it, or fallback to home
            const ref = document.referrer;
            if (ref && ref.trim() !== "" && !ref.includes('/admin/impersonate')) {
                window.location.href = ref;
            } else {
                window.location.href = "{{ url('/') }}";
            }
        }
    </script>

</body>
</html>
