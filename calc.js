// Motore di Calcolo Comparativo: Vecchio vs Nuovo Piano Agente Dojo

function calcolaVecito(contratti, transatoM1, transatoM2) {
    let gettoneBase = contratti * 200;
    let gara = 0;
    let c = contratti;
    if (c >= 5) { let p = Math.min(c, 8) - 4; if (p > 0) gara += p * 150; }
    if (c >= 9) { let p = Math.min(c, 12) - 8; if (p > 0) gara += p * 200; }
    if (c >= 13) { let p = Math.min(c, 16) - 12; if (p > 0) gara += p * 220; }
    if (c >= 17) { let p = Math.min(c, 20) - 16; if (p > 0) gara += p * 300; }
    if (c >= 21) { let p = Math.min(c, 24) - 20; if (p > 0) gara += p * 140; }
    if (c >= 25) { let p = Math.min(c, 28) - 24; if (p > 0) gara += p * 130; }
    if (c >= 29) { let p = c - 28; if (p > 0) gara += p * 50; }

    let m1 = transatoM1 * 0.0045;
    let m2 = transatoM2 * 0.0045;
    let totale = gettoneBase + gara + m1 + m2;
    return { gettoneBase, gara, m1, m2, totale };
}

function calcolaNuovo(contratti, transatoM1, sommaMarginaleGettoni, sommaM1M2Scaglioni, dailyCtoMedioM1) {
    let gettoneBase = contratti * 150;
    let gettoneMarginale = sommaMarginaleGettoni;
    let m1m2Nuovi = sommaM1M2Scaglioni;

    let velocity = 0;
    if (contratti >= 30) velocity = 2800 + (contratti - 30) * 50;
    else if (contratti >= 25) velocity = 1800;
    else if (contratti >= 20) velocity = 1050;
    else if (contratti >= 15) velocity = 550;
    else if (contratti >= 10) velocity = 250;
    else if (contratti >= 5) velocity = 50;

    // Milestone applicata ESCLUSIVAMENTE sul Daily CTO del primo mese (M1)
    let milestone = 0;
    if (dailyCtoMedioM1 >= 1666) milestone = 800;
    else if (dailyCtoMedioM1 >= 1000) milestone = 500;
    else if (dailyCtoMedioM1 >= 750) milestone = 300;
    else if (dailyCtoMedioM1 >= 500) milestone = 150;

    let totale = gettoneBase + gettoneMarginale + m1m2Nuovi + velocity + milestone;
    return { gettoneBase, gettoneMarginale, m1m2Nuovi, velocity, milestone, totale };
}
