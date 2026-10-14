// Motore di Calcolo Comparativo: Vecchio vs Nuovo Piano Agentile Dojo

function calcolaVecito(contratti, transatoTotale) {
    // 1. Gettone base: 200 € fissi per contratto
    let gettoneBase = contratti * 200;

    // 2. Bonus Gara Progressivo (Scaglioni marginali)
    let gara = 0;
    let c = contratti;
    if (c >= 5) {
        let pezzi5_8 = Math.min(c, 8) - 4;
        if (pezzi5_8 > 0) gara += pezzi5_8 * 150;
    }
    if (c >= 9) {
        let pezzi9_12 = Math.min(c, 12) - 8;
        if (pezzi9_12 > 0) gara += pezzi9_12 * 200;
    }
    if (c >= 13) {
        let pezzi13_16 = Math.min(c, 16) - 12;
        if (pezzi13_16 > 0) gara += pezzi13_16 * 220;
    }
    if (c >= 17) {
        let pezzi17_20 = Math.min(c, 20) - 16;
        if (pezzi17_20 > 0) gara += pezzi17_20 * 300;
    }
    if (c >= 21) {
        let pezzi21_24 = Math.min(c, 24) - 20;
        if (pezzi21_24 > 0) gara += pezzi21_24 * 140;
    }
    if (c >= 25) {
        let pezzi25_28 = Math.min(c, 28) - 24;
        if (pezzi25_28 > 0) gara += pezzi25_28 * 130;
    }
    if (c >= 29) {
        let pezzi29_plus = c - 28;
        if (pezzi29_plus > 0) gara += pezzi29_plus * 50;
    }

    // 3. M1 e M2 fissi allo 0.45% sul transato ciascuno
    let m1 = transatoTotale * 0.0045;
    let m2 = transatoTotale * 0.0045;

    let totale = gettoneBase + gara + m1 + m2;
    return { gettoneBase, gara, m1, m2, totale };
}

function calcolaNuovo(contratti, transatoTotale, sommaMarginaleGettoni, sommaM1M2Scaglioni, dailyCtoMedio) {
    // 1. Gettone base: 150 € per contratto
    let gettoneBase = contratti * 150;

    // 2. Gettone marginale (somma dei gettoni scaglione Daily CTO dei singoli client)
    let gettoneMarginale = sommaMarginaleGettoni;

    // 3. M1 e M2 Deferred (somma degli importi a scaglione dei singoli client)
    let m1m2Nuovi = sommaM1M2Scaglioni;

    // 4. Bonus Velocity (Soglie cumulative MID qualificati)
    let velocity = 0;
    if (contratti >= 30) {
        velocity = 2800 + (contratti - 30) * 50;
    } else if (contratti >= 25) {
        velocity = 1800;
    } else if (contratti >= 20) {
        velocity = 1050;
    } else if (contratti >= 15) {
        velocity = 550;
    } else if (contratti >= 10) {
        velocity = 250;
    } else if (contratti >= 5) {
        velocity = 50;
    }

    // 5. Milestone Daily CTO Medio Portafoglio
    let milestone = 0;
    if (dailyCtoMedio >= 1666) milestone = 800;
    else if (dailyCtoMedio >= 1000) milestone = 500;
    else if (dailyCtoMedio >= 750) milestone = 300;
    else if (dailyCtoMedio >= 500) milestone = 150;

    let totale = gettoneBase + gettoneMarginale + m1m2Nuovi + velocity + milestone;
    return { gettoneBase, gettoneMarginale, m1m2Nuovi, velocity, milestone, totale };
}
