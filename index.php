<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulatore Payout Agenti - Dojo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="max-w-5xl mx-auto">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-emerald-400">Simulatore Payout Agentile Dojo</h1>
            <p class="text-slate-400 mt-2">Carica il report CSV di Looker e confronta in tempo reale: Vecchio Piano vs Nuovo Piano</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Pannello Caricamento File -->
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 md:col-span-1">
                <h2 class="text-xl font-semibold mb-4 text-amber-400">Carica Report CSV</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm text-slate-300 mb-2">Seleziona file CSV (es. Looker):</label>
                        <input type="file" id="csvFile" accept=".csv" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-500 file:text-slate-950 hover:file:bg-emerald-600 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-300 mb-1">Seleziona Mese di Analisi:</label>
                        <select id="meseSelezionato" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-white">
                            <option value="2026-06">Giugno 2026</option>
                            <option value="2026-07">Luglio 2026</option>
                            <option value="2026-08">Agosto 2026</option>
                        </select>
                    </div>
                    <button onclick="elaboraCSV()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold py-2 px-4 rounded transition">Analizza e Calcola</button>
                </div>
            </div>

            <!-- Pannello Risultati Comparativi -->
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vecchio Piano -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700">
                    <h3 class="text-lg font-bold text-rose-400 mb-3">Vecchio Piano</h3>
                    <div id="risultatoVecchio" class="space-y-2 text-sm text-slate-300">
                        <p class="text-slate-500 italic">Carica un file CSV per visualizzare i dati...</p>
                    </div>
                </div>

                <!-- Nuovo Piano -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-emerald-500/30">
                    <h3 class="text-lg font-bold text-emerald-400 mb-3">Nuovo Piano</h3>
                    <div id="risultatoNuovo" class="space-y-2 text-sm text-slate-300">
                        <p class="text-slate-500 italic">Carica un file CSV per visualizzare i dati...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Box Differenza Netta -->
        <div id="boxDifferenza" class="bg-slate-800 border border-slate-700 p-6 rounded-xl text-center">
            <h3 class="text-xl font-semibold text-slate-300">Differenza Economica</h3>
            <p id="testoDifferenza" class="text-2xl font-bold text-slate-400 mt-2">In attesa di caricamento file...</p>
        </div>
    </div>

    <script src="calc.js"></script>
    <script>
        function elaboraCSV() {
            let fileInput = document.getElementById('csvFile');
            let meseColPrefix = document.getElementById('meseSelezionato').value;

            if (fileInput.files.length === 0) {
                alert("Seleziona prima un file CSV!");
                return;
            }

            let file = fileInput.files[0];
            Papa.parse(file, {
                header: true,
                dynamicTyping: true,
                skipEmptyLines: true,
                complete: function(results) {
                    let data = results.data;
                    let transatoCol = null;

                    // Trova la colonna esatta del transato per il mese scelto
                    if (data.length > 0) {
                        let sampleKeys = Object.keys(data[0]);
                        transatoCol = sampleKeys.find(k => k.startsWith(meseColPrefix) && k.includes("Total Transaction Amount"));
                    }

                    if (!transatoCol) {
                        alert("Impossibile trovare la colonna del transato per il mese selezionato nel CSV.");
                        return;
                    }

                    let contratti = 0;
                    let transatoTotale = 0;
                    let sommaMarginaleGettoni = 0;
                    let sommaM1M2Scaglioni = 0;

                    data.forEach(row => {
                        let val = parseFloat(row[transatoCol]) || 0;
                        if (val > 0) {
                            contratti++;
                            transatoTotale += val;

                            // Calcolo gettone marginale nuovo piano (basato su stima annuale o mensile)
                            // Qui mappiamo sul Daily CTO stimato (supponendo 30 giorni)
                            let dailyCtoItem = val / 30;
                            let gettoneMar = 15;
                            if (dailyCtoItem > 1650) gettoneMar = 180;
                            else if (dailyCtoItem > 1100) gettoneMar = 160;
                            else if (dailyCtoItem > 685) gettoneMar = 140;
                            else if (dailyCtoItem > 410) gettoneMar = 70;
                            else if (dailyCtoItem > 270) gettoneMar = 40;
                            else if (dailyCtoItem > 100) gettoneMar = 20;
                            sommaMarginaleGettoni += gettoneMar;

                            // Calcolo M1/M2 a scaglioni nuovo piano sul mensile dell'attività
                            let scaglioneM = 0;
                            if (val > 50000) scaglioneM = 500;
                            else if (val >= 20000) scaglioneM = 225;
                            else if (val >= 15000) scaglioneM = 115;
                            else if (val >= 10000) scaglioneM = 75;
                            else if (val >= 7500) scaglioneM = 40;
                            else if (val >= 5000) scaglioneM = 15;
                            sommaM1M2Scaglioni += (scaglioneM * 2); // M1 + M2
                        }
                    });

                    let dailyCtoMedio = (transatoTotale / (contratti || 1)) / 30;

                    let vecchio = calcolaVecito(contratti, transatoTotale);
                    let nuovo = calcolaNuovo(contratti, transatoTotale, sommaMarginaleGettoni, sommaM1M2Scaglioni, dailyCtoMedio);

                    // Mostra risultati Vecchio
                    document.getElementById('risultatoVecchio').innerHTML = `
                        <div class="flex justify-between"><span>Contratti Attivi:</span> <b>${contratti}</b></div>
                        <div class="flex justify-between"><span>Transato Totale:</span> <b>€ ${transatoTotale.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b></div>
                        <div class="flex justify-between"><span>Gettone Base (200€):</span> <b>€ ${vecchio.gettoneBase.toFixed(2)}</b></div>
                        <div class="flex justify-between"><span>Bonus Gara:</span> <b>€ ${vecchio.gara.toFixed(2)}</b></div>
                        <div class="flex justify-between"><span>M1 & M2 (0.9% tot):</span> <b>€ ${(vecchio.m1 + vecchio.m2).toFixed(2)}</b></div>
                        <hr class="border-slate-700 my-2">
                        <div class="flex justify-between text-base font-bold text-white"><span>Totale Vecchio:</span> <span class="text-rose-400">€ ${vecchio.totale.toFixed(2)}</span></div>
                    `;

                    // Mostra risultati Nuovo
                    document.getElementById('risultatoNuovo').innerHTML = `
                        <div class="flex justify-between"><span>Contratti Attivi:</span> <b>${contratti}</b></div>
                        <div class="flex justify-between"><span>Transato Totale:</span> <b>€ ${transatoTotale.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b></div>
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
            });
        }
    </script>
</body>
</html>
