<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulatore Payout Agenti - Dojo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="max-w-6xl mx-auto">
        <header class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-emerald-400">Simulatore Payout Agenti Dojo</h1>
            <p class="text-slate-400 mt-2">Confronto in tempo reale: Vecchio Piano vs Nuovo Piano (Gestione Multi-Location)</p>
        </header>

        <!-- Box Guida Operativa -->
        <div class="bg-slate-800 border border-amber-500/40 p-6 rounded-xl mb-8 relative">
            <h2 class="text-lg font-bold text-amber-400 mb-2">Come ottenere il file corretto:</h2>
            <ol class="list-decimal list-inside space-y-1.5 text-sm text-slate-300 mb-4">
                <li>Apri l'<b>email del CTO inviata da Dojo</b> nei primi giorni del mese successivo.</li>
                <li>Vai sulla stampa del report, crealo come <b>PDF</b> e scaricalo.</li>
                <li>Chiedi a <b>Gemini</b> di convertire i dati in un file <b>Excel (.xlsx)</b> usando il prompt qui sotto (già ottimizzato per formattare i numeri in modo pulito).</li>
                <li>Carica il file ed incolla l'elenco dei clienti nuovi: il sistema sommerà in automatico tutte le location collegate.</li>
            </ol>

            <!-- Box Prompt Copiabile -->
            <div class="bg-slate-900 p-4 rounded-lg border border-slate-700 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <code id="promptText" class="text-xs text-slate-300 font-mono select-all">Ho allegato il report PDF del CTO inviato da Dojo. Estrai i dati tabulari e convertili in Excel (.xlsx). Regole rigorose per i numeri: 1. Includi solo i primi due mesi, escludendo il terzo. 2. I valori di transato devono essere scritti come numeri puri (es. 129645.43 oppure 129645,43), SENZA il simbolo dell'euro (€), SENZA virgole per le migliaia e SENZA percentuali o parentesi. 3. Mantieni le colonne dei nomi e manager intatte.</code>
                <button onclick="copiaPrompt()" id="btnCopia" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-1.5 px-4 rounded text-xs transition whitespace-nowrap">Copia Prompt</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Pannello Caricamento File e Filtro Clienti -->
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 md:col-span-1 space-y-4">
                <h2 class="text-xl font-semibold text-amber-400">Parametri Analisi</h2>
                <div>
                    <label class="block text-sm text-slate-300 mb-2">Seleziona file (.xlsx o .csv):</label>
                    <input type="file" id="dataFile" accept=".csv, .xlsx, .xls" class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-500 file:text-slate-950 hover:file:bg-emerald-600 cursor-pointer">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Elenco Clienti Nuovi (uno per riga o separati da virgola):</label>
                    <textarea id="elencoClienti" rows="6" class="w-full bg-slate-900 border border-slate-700 rounded p-2 text-xs text-white" placeholder="Incolla qui i nomi dei clienti nuovi... Il sistema cercherà tutte le righe/location corrispondenti."></textarea>
                </div>
                <button onclick="elaboraFile()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-bold py-2 px-4 rounded transition">Analizza e Calcola</button>
            </div>

            <!-- Pannello Risultati Comparativi -->
            <div class="bg-slate-800 p-6 rounded-xl border border-slate-700 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Vecchio Piano -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700">
                    <h3 class="text-lg font-bold text-rose-400 mb-3">Vecchio Piano</h3>
                    <div id="risultatoVecchio" class="space-y-2 text-sm text-slate-300">
                        <p class="text-slate-500 italic">Carica il file e avvia il calcolo...</p>
                    </div>
                </div>

                <!-- Nuovo Piano -->
                <div class="bg-slate-900/50 p-4 rounded-lg border border-emerald-500/30">
                    <h3 class="text-lg font-bold text-emerald-400 mb-3">Nuovo Piano</h3>
                    <div id="risultatoNuovo" class="space-y-2 text-sm text-slate-300">
                        <p class="text-slate-500 italic">Carica il file e avvia il calcolo...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Box Differenza Netta -->
        <div id="boxDifferenza" class="bg-slate-800 border border-slate-700 p-6 rounded-xl text-center mb-12">
            <h3 class="text-xl font-semibold text-slate-300">Differenza Economica</h3>
            <p id="testoDifferenza" class="text-2xl font-bold text-slate-400 mt-2">In attesa di elaborazione...</p>
        </div>

        <!-- Sezione Regole Applicate -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-slate-800/60 p-6 rounded-xl border border-slate-700">
            <div>
                <h3 class="text-lg font-bold text-rose-400 mb-3">Regole Vecchio Sistema</h3>
                <ul class="list-disc list-inside space-y-1 text-sm text-slate-300">
                    <li><b>Gettone Base:</b> € 200 fissi per ogni contratto/location attivata.</li>
                    <li><b>Bonus Gara:</b> Sistema a scaglioni progressivi marginali (5-8 pz a 150€, 9-12 a 200€, ecc.).</li>
                    <li><b>M1:</b> 0,45% calcolato sul transato del primo mese del file.</li>
                    <li><b>M2:</b> 0,45% calcolato sul transato del secondo mese del file.</li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold text-emerald-400 mb-3">Regole Nuovo Sistema</h3>
                <ul class="list-disc list-inside space-y-1 text-sm text-slate-300">
                    <li><b>Gettone Base:</b> € 150 fissi per contratto/location.</li>
                    <li><b>Gettone Marginale:</b> Da € 15 a € 180 in base allo scaglione di Daily CTO stimato per location.</li>
                    <li><b>M1 & M2 Deferred:</b> Compensi fissi a scaglioni mensili (da € 15 a € 500 per mese per location).</li>
                    <li><b>Bonus Velocity:</b> Soglie cumulative basate sul totale delle location/MID qualificati.</li>
                    <li><b>Milestone CTO:</b> Premio qualità basato sulla media del Daily CTO del portafoglio.</li>
                </ul>
            </div>
        </div>
    </div>

    <script src="calc.js?v=10.0"></script>
    <script>
        function copiaPrompt() {
            let text = document.getElementById('promptText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                let btn = document.getElementById('btnCopia');
                btn.innerText = "Copiato!";
                btn.className = "bg-emerald-500 text-slate-950 font-bold py-1.5 px-4 rounded text-xs transition whitespace-nowrap";
                setTimeout(() => {
                    btn.innerText = "Copia Prompt";
                    btn.className = "bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold py-1.5 px-4 rounded text-xs transition whitespace-nowrap";
                }, 2000);
            });
        }

        function elaboraFile() {
            let fileInput = document.getElementById('dataFile');
            if (fileInput.files.length === 0) {
                alert("Seleziona prima un file Excel (.xlsx) o CSV!");
                return;
            }

            let file = fileInput.files[0];
            let reader = new FileReader();
            let extension = file.name.split('.').pop().toLowerCase();

            if (extension === 'xlsx' || extension === 'xls') {
                reader.onload = function(e) {
                    let data = new Uint8Array(e.target.result);
                    let workbook = XLSX.read(data, {type: 'array'});
                    let firstSheetName = workbook.SheetNames[0];
                    let worksheet = workbook.Sheets[firstSheetName];
                    let json = XLSX.utils.sheet_to_json(worksheet);
                    processData(json);
                };
                reader.readAsArrayBuffer(file);
            } else {
                Papa.parse(file, {
                    header: true,
                    dynamicTyping: true,
                    skipEmptyLines: true,
                    complete: function(results) {
                        processData(results.data);
                    }
                });
            }
        }

        function parseValore(val) {
            if (typeof val === 'number') return val;
            if (!val) return 0;
            let pulito = val.toString().split('(')[0].replace(/[€\s]/g, '').replace(/,/g, '');
            return parseFloat(pulito) || 0;
        }

        function processData(data) {
            if (!data || data.length === 0) {
                alert("Il file è vuoto o non leggibile.");
                return;
            }

            let rawText = document.getElementById('elencoClienti').value.trim();
            let clientiFiltrati = [];
            if (rawText !== "") {
                clientiFiltrati = rawText.split(/[\n,]+/).map(s => s.trim().toLowerCase()).filter(s => s.length > 0);
            }

            let sampleKeys = Object.keys(data[0]);
            let nameCol = sampleKeys.find(k => k.toLowerCase().includes('cliente') || k.toLowerCase().includes('name') || k.toLowerCase().includes('activity') || k.toLowerCase().includes('azienda')) || sampleKeys[1];
            let colM1 = sampleKeys[sampleKeys.length - 2];
            let colM2 = sampleKeys[sampleKeys.length - 1];

            let contratti = 0;
            let transatoM1Totale = 0;
            let transatoM2Totale = 0;
            let sommaMarginaleGettoni = 0;
            let sommaM1M2Scaglioni = 0;

            data.forEach(row => {
                let nomeCliente = row[nameCol] ? row[nameCol].toString().toLowerCase() : "";
                
                if (clientiFiltrati.length > 0) {
                    let match = clientiFiltrati.some(c => nomeCliente.includes(c));
                    if (!match) return;
                }

                let v1 = parseValore(row[colM1]);
                let v2 = parseValore(row[colM2]);

                if (v1 > 0 || v2 > 0) {
                    contratti++;
                    transatoM1Totale += v1;
                    transatoM2Totale += v2;

                    let dailyCtoItem = v1 / 30;
                    let gettoneMar = 15;
                    if (dailyCtoItem > 1650) gettoneMar = 180;
                    else if (dailyCtoItem > 1100) gettoneMar = 160;
                    else if (dailyCtoItem > 685) gettoneMar = 140;
                    else if (dailyCtoItem > 410) gettoneMar = 70;
                    else if (dailyCtoItem > 270) gettoneMar = 40;
                    else if (dailyCtoItem > 100) gettoneMar = 20;
                    sommaMarginaleGettoni += gettoneMar;

                    function getScaglioneM(val) {
                        if (val > 50000) return 500;
                        if (val >= 20000) return 225;
                        if (val >= 15000) return 115;
                        if (val >= 10000) return 75;
                        if (val >= 7500) return 40;
                        if (val >= 5000) return 15;
                        return 0;
                    }
                    sommaM1M2Scaglioni += getScaglioneM(v1) + getScaglioneM(v2);
                }
            });

            let transatoTotalePortafoglio = transatoM1Totale + transatoM2Totale;
            let dailyCtoMedio = (transatoM1Totale / (contratti || 1)) / 30;

            let vecchio = calcolaVecito(contratti, transatoM1Totale, transatoM2Totale);
            let nuovo = calcolaNuovo(contratti, transatoTotalePortafoglio, sommaMarginaleGettoni, sommaM1M2Scaglioni, dailyCtoMedio);

            document.getElementById('risultatoVecchio').innerHTML = `
                <div class="flex justify-between"><span>Location / Contratti Attivi:</span> <b>${contratti}</b></div>
                <div class="flex justify-between"><span>Transato M1:</span> <b>€ ${transatoM1Totale.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b></div>
                <div class="flex justify-between"><span>Transato M2:</span> <b>€ ${transatoM2Totale.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b></div>
                <div class="flex justify-between"><span>Gettone Base (200€):</span> <b>€ ${vecchio.gettoneBase.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>Bonus Gara:</span> <b>€ ${vecchio.gara.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>M1 (0.45%):</span> <b>€ ${vecchio.m1.toFixed(2)}</b></div>
                <div class="flex justify-between"><span>M2 (0.45%):</span> <b>€ ${vecchio.m2.toFixed(2)}</b></div>
                <hr class="border-slate-700 my-2">
                <div class="flex justify-between text-base font-bold text-white"><span>Totale Vecchio:</span> <span class="text-rose-400">€ ${vecchio.totale.toFixed(2)}</span></div>
            `;

            document.getElementById('risultatoNuovo').innerHTML = `
                <div class="flex justify-between"><span>Location / Contratti Attivi:</span> <b>${contratti}</b></div>
                <div class="flex justify-between"><span>Transato Totale:</span> <b>€ ${transatoTotalePortafoglio.toLocaleString('it-IT', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</b></div>
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
                box.className = "bg-emerald-950/40 border border-emerald-500/50 p-6 rounded-xl text-center mb-12";
                testo.innerHTML = `Con il nuovo piano guadagni <span class="text-emerald-400">+€ ${diff.toFixed(2)}</span> in più!`;
            } else {
                box.className = "bg-rose-950/40 border border-rose-500/50 p-6 rounded-xl text-center mb-12";
                testo.innerHTML = `Il vecchio piano rendeva <span class="text-rose-400">€ ${Math.abs(diff).toFixed(2)}</span> in più.`;
            }
        }
    </script>
</body>
</html>
