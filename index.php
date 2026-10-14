<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulatore Payout Agenti - Dojo</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="max-w-5xl mx-auto">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-emerald-400">Simulatore Payout Agentile Dojo</h1>
            <p class="text-slate-400 mt-2">Confronto in tempo reale: Vecchio Piano vs Nuovo Piano</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Pannello Input -->
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 md:col-span-1">
                <h2 class="text-xl font-semibold mb-4 text-amber-400">Dati Agente</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Numero Contratti (MID):</label>
                        <input type="number" id="contratti" value="10" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Transato Totale (€):</label>
                        <input type="number" id="transatoTotale" value="50000" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Somma Gettoni Marginali (€):</label>
                        <input type="number" id="marginaleGettoni" value="400" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white" placeholder="Es. somma scaglioni CTO">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Somma M1/M2 Scaglioni Nuovi (€):</label>
                        <input type="number" id="m1m2NuoviScaglioni" value="300" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Daily CTO Medio (€/giorno):</label>
                        <input type="number" id="dailyCto" value="600" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white">
                    </div>
                    <button onclick="eseguiSimulazione()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold py-2 px-4 rounded transition">Calcola Simulazione</button>
                </div>
            </div>

            <!-- Pannello Risultati Comparativi -->
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vecchio Piano -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700">
                    <h3 class="text-lg font-bold text-rose-400 mb-3">Vecchio Piano</h3>
                    <div id="risultatoVecchio" class="space-y-2 text-sm text-slate-300">
                        <!-- Output JS -->
                    </div>
                </div>

                <!-- Nuovo Piano -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-emerald-500/30">
                    <h3 class="text-lg font-bold text-emerald-400 mb-3">Nuovo Piano</h3>
                    <div id="risultatoNuovo" class="space-y-2 text-sm text-slate-300">
                        <!-- Output JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Box Differenza Netta -->
        <div id="boxDifferenza" class="bg-emerald-950/40 border border-emerald-500/50 p-6 rounded-xl text-center">
            <h3 class="text-xl font-semibold text-emerald-300">Differenza Economica</h3>
            <p id="testoDifferenza" class="text-3xl font-extrabold text-white mt-2">In attesa di calcolo...</p>
        </div>
    </div>

    <script src="calc.js"></script>
    <script>
        function eseguiSimulazione() {
            let contratti = parseInt(document.getElementById('contratti').value) || 0;
            let transatoTotale = parseFloat(document.getElementById('transatoTotale').value) || 0;
            let marginaleGettoni = parseFloat(document.getElementById('marginaleGettoni').value) || 0;
            let m1m2NuoviScaglioni = parseFloat(document.getElementById('m1m2NuoviScaglioni').value) || 0;
            let dailyCto = parseFloat(document.getElementById('dailyCto').value) || 0;

            let vecchio = calcolaVecito(contratti, transatoTotale);
            let nuovo = calcolaNuovo(contratti, transatoTotale, marginaleGettoni, m1m2NuoviScaglioni, dailyCto);

            document.getElementById('risultatoVecchio').innerHTML = `
                <div class="flex justify-between"><span>Gettone Base (200€):</span> <b>€ ${vecchio.gettoneBase.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>Bonus Gara:</span> <b>€ ${vecchio.gara.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>M1 (0.45%):</span> <b>€ ${vecchio.m1.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>M2 (0.45%):</span> <b>€ ${vecchio.m2.toFixed(2)}</b></div>
                <hr class="border-slate-700 my-2">
                <div class="flex justify-between text-base font-bold text-white"><span>Totale Vecchio:</span> <span class="text-rose-400">€ ${vecchio.totale.toFixed(2)}</span></div>
            `;

            document.getElementById('risultatoNuovo').innerHTML = `
                <div class="flex justify-between"><span>Gettone Base (150€):</span> <b>€ ${nuovo.gettoneBase.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>Gettone Marginale:</span> <b>€ ${nuovo.gettoneMarginale.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>M1 & M2 (Scaglioni):</span> <b>€ ${nuovo.m1m2Nuovi.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>Bonus Velocity:</span> <b>€ ${nuovo.velocity.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>Milestone CTO:</span> <b>€ ${nuovo.milestone.toFixed(2)}</b></div>
                <hr class="border-slate-700 my-2">
                <div class="flex justify-between text-base font-bold text-white"><span>Totale Nuovo:</span> <span class="text-emerald-400">€ ${nuovo.totale.toFixed(2)}</span></div>
            `;

            let diff = nuovo.totale - vecchio.totale;
            let box = document.getElementById('boxDifferenza');
            let testo = document.getElementById('testoDifferenza');

            if (diff >= 0) {
                box.className = "bg-emerald-950/40 border border-emerald-500/50 p-6 rounded-xl text-center";
                testo.innerHTML = `Con il nuovo piano guadagni <span class="text-emerald-400">+€ ${diff.toFixed(2)}</span> in più!`;
            } else {
                box.className = "bg-rose-950/40 border border-rose-500/50 p-6 rounded-xl text-center";
                testo.innerHTML = `Il vecchio piano rendeva <span class="text-rose-400">€ ${Math.abs(diff).toFixed(2)}</span> in più.`;
            }
        }

        // Esegui al caricamento
        window.onload = eseguiSimulazione;
    </script>
</body>
</html>
