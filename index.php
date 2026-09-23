<?php session_start(); require_once "csrf.php"; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
    <meta name="description" content="Vie à deux — Site de rencontres sérieuses. Rejoignez des milliers de célibataires près de chez vous. Inscription gratuite."/>
  <meta name="keywords" content="rencontres, célibataires, amour, site de rencontres, sérieux"/>
  <meta property="og:title" content="Vie à deux — Trouvez votre complice"/>
  <meta property="og:description" content="Rejoignez des milliers de célibataires sincères près de chez vous."/>
  <meta property="og:type" content="website"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Vie à deux — Trouvez votre complice</title>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --rouge:    #8B1A1A;
      --rouge-clair: #B94040;
      --creme:    #FAF6F0;
      --beige:    #EDE5D8;
      --brun:     #3A2218;
      --gris:     #7A6E68;
      --blanc:    #FFFFFF;
      --ombre:    0 8px 40px rgba(58,34,24,0.12);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--creme);
      color: var(--brun);
      overflow-x: hidden;
    }

    /* ── NAV ── */
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 5vw;
      height: 72px;
      background: rgba(139,26,26,0.97);
      backdrop-filter: blur(10px);
      box-shadow: 0 2px 20px rgba(0,0,0,0.2);
    }
    .nav-logo {
      display: flex; align-items: center; gap: 10px;
      color: var(--blanc); text-decoration: none;
    }
    .nav-logo svg { width: 38px; height: 38px; }
    .nav-logo span {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.55rem; font-weight: 600; letter-spacing: 0.02em;
    }
    .nav-links { display: flex; gap: 8px; align-items: center; }
    .nav-links a {
      color: rgba(255,255,255,0.88);
      text-decoration: none; font-size: 0.88rem; font-weight: 400;
      padding: 8px 18px; border-radius: 40px;
      transition: all .25s;
      letter-spacing: 0.04em;
    }
    .nav-links a:hover { background: rgba(255,255,255,0.12); color: #fff; }
    .nav-links .btn-nav {
      background: var(--blanc); color: var(--rouge);
      font-weight: 500; padding: 9px 22px;
    }
    .nav-links .btn-nav:hover { background: var(--creme); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,0,0,0.15); }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      display: flex; align-items: center;
      background:
        linear-gradient(135deg, rgba(139,26,26,0.82) 0%, rgba(58,34,24,0.65) 50%, rgba(0,0,0,0.4) 100%),
        url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=1600&q=80') center/cover no-repeat;
      padding: 100px 5vw 60px;
      position: relative;
      overflow: hidden;
    }
    .hero::after {
      content: '';
      position: absolute; bottom: 0; left: 0; right: 0; height: 120px;
      background: linear-gradient(to top, var(--creme), transparent);
    }
    .hero-inner {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 60px; align-items: center;
      max-width: 1200px; margin: 0 auto; width: 100%;
      position: relative; z-index: 1;
    }
    .hero-text { color: var(--blanc); }
    .hero-eyebrow {
      display: inline-block;
      font-size: 0.78rem; font-weight: 500; letter-spacing: 0.18em;
      text-transform: uppercase;
      background: rgba(255,255,255,0.18);
      border: 1px solid rgba(255,255,255,0.3);
      padding: 6px 16px; border-radius: 40px; margin-bottom: 24px;
      animation: fadeUp .7s both;
    }
    .hero-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2.6rem, 5vw, 4rem);
      font-weight: 300; line-height: 1.15;
      margin-bottom: 22px;
      animation: fadeUp .7s .15s both;
    }
    .hero-title em { font-style: italic; color: #f0c0c0; }
    .hero-sub {
      font-size: 1rem; line-height: 1.7; color: rgba(255,255,255,0.82);
      margin-bottom: 36px; max-width: 420px;
      animation: fadeUp .7s .25s both;
    }
    .hero-badges {
      display: flex; gap: 12px; flex-wrap: wrap;
      animation: fadeUp .7s .35s both;
    }
    .badge {
      display: flex; align-items: center; gap: 6px;
      font-size: 0.82rem; color: rgba(255,255,255,0.75);
    }
    .badge svg { width: 16px; height: 16px; flex-shrink: 0; }

    /* ── CARD INSCRIPTION ── */
    .hero-card {
      background: var(--blanc);
      border-radius: 20px;
      padding: 40px 36px;
      box-shadow: 0 24px 80px rgba(0,0,0,0.28);
      animation: fadeUp .7s .2s both;
    }
    .card-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.7rem; font-weight: 600; color: var(--brun);
      margin-bottom: 6px;
    }
    .card-sub { font-size: 0.84rem; color: var(--gris); margin-bottom: 28px; }

    .form-group { margin-bottom: 16px; }
    .form-group label {
      display: block; font-size: 0.78rem; font-weight: 500;
      color: var(--gris); margin-bottom: 6px; letter-spacing: 0.05em;
      text-transform: uppercase;
    }
    .form-group select,
    .form-group input {
      width: 100%;
      padding: 12px 16px;
      border: 1.5px solid var(--beige);
      border-radius: 10px;
      font-family: 'DM Sans', sans-serif;
      font-size: 0.9rem;
      color: var(--brun);
      background: var(--creme);
      transition: border-color .2s, box-shadow .2s;
      outline: none;
      appearance: none;
    }
    .form-group select:focus,
    .form-group input:focus {
      border-color: var(--rouge);
      box-shadow: 0 0 0 3px rgba(139,26,26,0.1);
      background: var(--blanc);
    }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .btn-primary {
      display: block; width: 100%;
      padding: 15px;
      background: var(--rouge);
      color: var(--blanc);
      font-family: 'DM Sans', sans-serif;
      font-size: 0.95rem; font-weight: 500; letter-spacing: 0.04em;
      border: none; border-radius: 12px; cursor: pointer;
      transition: all .25s;
      margin-top: 20px;
    }
    .btn-primary:hover {
      background: var(--rouge-clair);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(139,26,26,0.35);
    }
    .card-legal {
      font-size: 0.72rem; color: var(--gris); text-align: center;
      margin-top: 14px; line-height: 1.5;
    }
    .card-legal a { color: var(--rouge); text-decoration: none; }

    /* ── HOW IT WORKS ── */
    .section { padding: 90px 5vw; }
    .section-label {
      font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase;
      color: var(--rouge); font-weight: 500; margin-bottom: 10px;
    }
    .section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: clamp(2rem, 4vw, 3rem); font-weight: 400;
      color: var(--brun); line-height: 1.2; margin-bottom: 16px;
    }
    .section-sub { color: var(--gris); max-width: 500px; line-height: 1.7; font-size: 0.95rem; }

    .steps-grid {
      display: grid; grid-template-columns: repeat(3, 1fr);
      gap: 32px; margin-top: 56px; max-width: 1100px; margin-left: auto; margin-right: auto;
    }
    .step-card {
      background: var(--blanc);
      border-radius: 18px; padding: 36px 30px;
      box-shadow: var(--ombre);
      position: relative; overflow: hidden;
      transition: transform .3s, box-shadow .3s;
    }
    .step-card:hover { transform: translateY(-6px); box-shadow: 0 20px 60px rgba(58,34,24,0.16); }
    .step-card::before {
      content: attr(data-num);
      position: absolute; top: -10px; right: 20px;
      font-family: 'Cormorant Garamond', serif;
      font-size: 7rem; font-weight: 600;
      color: rgba(139,26,26,0.06); line-height: 1;
    }
    .step-icon {
      width: 56px; height: 56px; border-radius: 14px;
      background: linear-gradient(135deg, var(--rouge), var(--rouge-clair));
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 22px;
    }
    .step-icon svg { width: 26px; height: 26px; color: var(--blanc); }
    .step-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.35rem; font-weight: 600; color: var(--brun); margin-bottom: 10px;
    }
    .step-desc { font-size: 0.88rem; color: var(--gris); line-height: 1.7; }

    /* ── PROFILES ── */
    .profiles-section { background: var(--brun); padding: 90px 5vw; }
    .profiles-section .section-title { color: var(--blanc); }
    .profiles-section .section-label { color: #f0c0c0; }
    .profiles-section .section-sub { color: rgba(255,255,255,0.6); }

    .profiles-grid {
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 24px; margin-top: 52px; max-width: 1100px; margin-left: auto; margin-right: auto;
    }
    .profile-card {
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      border-radius: 18px; overflow: hidden;
      transition: transform .3s, background .3s;
      cursor: pointer;
    }
    .profile-card:hover { transform: translateY(-6px); background: rgba(255,255,255,0.1); }
    .profile-img {
      width: 100%; aspect-ratio: 3/4; object-fit: cover;
      display: block; filter: brightness(0.9);
      transition: filter .3s;
    }
    .profile-card:hover .profile-img { filter: brightness(1); }
    .profile-info { padding: 18px 20px; }
    .profile-name {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.15rem; font-weight: 600; color: var(--blanc);
    }
    .profile-age { font-size: 0.82rem; color: rgba(255,255,255,0.5); margin-top: 2px; }
    .profile-tags { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
    .tag {
      font-size: 0.7rem; padding: 3px 10px; border-radius: 40px;
      background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.65);
    }
    .profile-btn {
      display: block; margin: 14px 20px 20px;
      text-align: center; padding: 10px;
      background: var(--rouge); color: var(--blanc);
      border-radius: 10px; font-size: 0.82rem; font-weight: 500;
      text-decoration: none; transition: background .2s;
    }
    .profile-btn:hover { background: var(--rouge-clair); }

    /* ── SECURITY ── */
    .security-section {
      padding: 90px 5vw;
      background: var(--creme);
    }
    .security-inner {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 80px; align-items: center;
      max-width: 1100px; margin: 0 auto;
    }
    .security-visual {
      background: linear-gradient(135deg, var(--rouge) 0%, var(--brun) 100%);
      border-radius: 24px; padding: 50px 40px;
      display: flex; flex-direction: column; gap: 22px;
    }
    .security-item {
      display: flex; align-items: flex-start; gap: 16px;
      background: rgba(255,255,255,0.1); border-radius: 14px; padding: 18px 20px;
    }
    .security-item svg { width: 22px; height: 22px; color: #f0c0c0; flex-shrink: 0; margin-top: 2px; }
    .security-item-text h4 { color: var(--blanc); font-size: 0.95rem; font-weight: 500; margin-bottom: 4px; }
    .security-item-text p { color: rgba(255,255,255,0.6); font-size: 0.8rem; line-height: 1.5; }

    /* ── STATS ── */
    .stats-bar {
      background: var(--rouge);
      padding: 50px 5vw;
      display: flex; justify-content: center; gap: 80px;
      flex-wrap: wrap;
    }
    .stat { text-align: center; }
    .stat-num {
      font-family: 'Cormorant Garamond', serif;
      font-size: 3rem; font-weight: 600; color: var(--blanc);
      line-height: 1;
    }
    .stat-label { font-size: 0.82rem; color: rgba(255,255,255,0.7); margin-top: 6px; letter-spacing: 0.05em; }

    /* ── FOOTER ── */
    footer {
      background: var(--brun); color: rgba(255,255,255,0.55);
      padding: 60px 5vw 30px;
    }
    .footer-top {
      display: grid; grid-template-columns: 2fr 1fr 1fr 1fr;
      gap: 48px; margin-bottom: 48px;
      max-width: 1100px; margin-left: auto; margin-right: auto;
    }
    .footer-logo {
      font-family: 'Cormorant Garamond', serif;
      font-size: 1.4rem; color: var(--blanc); margin-bottom: 14px;
    }
    .footer-desc { font-size: 0.83rem; line-height: 1.7; max-width: 260px; }
    .footer-col h5 {
      color: var(--blanc); font-size: 0.82rem; font-weight: 500;
      letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 16px;
    }
    .footer-col a {
      display: block; color: rgba(255,255,255,0.5); text-decoration: none;
      font-size: 0.83rem; margin-bottom: 10px; transition: color .2s;
    }
    .footer-col a:hover { color: var(--blanc); }
    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.08);
      padding-top: 24px; text-align: center; font-size: 0.78rem;
      max-width: 1100px; margin-left: auto; margin-right: auto;
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(28px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .reveal { opacity: 0; transform: translateY(30px); transition: opacity .65s, transform .65s; }
    .reveal.visible { opacity: 1; transform: none; }

    /* ── RESPONSIVE ── */
    .critere {
      font-size: 0.78rem; color: #B0A8A0;
      padding: 3px 0; transition: color .2s;
      display: flex; align-items: center; gap: 6px;
    }
    .critere.ok { color: #2E7D32; }
    .critere.ok::first-letter { content: "✓"; }
    .critere { font-size:0.78rem; color:#B0A8A0; padding:2px 0; transition:color .2s; }
    .critere.ok { color:#27ae60; }
    @media (max-width: 900px) {
      .hero-inner { grid-template-columns: 1fr; }
      .hero-card { max-width: 480px; }
      .steps-grid, .profiles-grid { grid-template-columns: 1fr 1fr; }
      .security-inner { grid-template-columns: 1fr; }
      .footer-top { grid-template-columns: 1fr 1fr; }
      .stats-bar { gap: 40px; }
    }
    @media (max-width: 580px) {
      .steps-grid, .profiles-grid { grid-template-columns: 1fr; }
      .footer-top { grid-template-columns: 1fr; }
      .form-row { grid-template-columns: 1fr; }
      .nav-links a:not(.btn-nav) { display: none; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">
    <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M20 34s-14-8.5-14-18a8 8 0 0 1 14-5.3A8 8 0 0 1 34 16c0 9.5-14 18-14 18z" fill="rgba(255,255,255,0.2)" stroke="rgba(255,255,255,0.8)" stroke-width="1.5"/>
      <path d="M12 20s4-3 8 0 8 0 8 0" stroke="rgba(255,255,255,0.6)" stroke-width="1.2" stroke-linecap="round"/>
    </svg>
    <span>Vie à deux</span>
  </a>
  <div class="nav-links">
    <a href="#comment">Comment ça marche</a>
    <a href="#profils">Profils</a>
    <a href="#securite">Sécurité</a>
    <a href="connexion.php" class="btn-nav">Connexion</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-text">
      <div class="hero-eyebrow">✦ Rencontres sérieuses en France</div>
      <h1 class="hero-title">
        Pour que demain ne se vive plus <em>seul</em>
      </h1>
      <p class="hero-sub">
        Rejoignez des milliers de célibataires sincères près de chez vous. Vie à deux, c'est la rencontre authentique, dans un cadre bienveillant et sécurisé.
      </p>
      <div class="hero-badges">
        <span class="badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          Inscription 100% gratuite
        </span>
        <span class="badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Profils vérifiés
        </span>
        <span class="badge">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
          Réponse en moins de 24h
        </span>
      </div>
    </div>

    <!-- CARD -->
    <div class="hero-card">
      <p class="card-title">Commencer maintenant</p>
      <p class="card-sub">Créez votre profil en moins de 2 minutes</p>

      <form method="POST" action="inscription.php">
        <?php echo csrf_field(); ?>

        <div class="form-row">
          <div class="form-group">
            <label>Prénom</label>
            <input type="text" name="prenom" placeholder="Votre prénom" required />
          </div>
          <div class="form-group">
            <label>Nom</label>
            <input type="text" name="nom" placeholder="Votre nom" required />
          </div>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="votre@email.com" required />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Je suis</label>
            <select name="je_suis" required>
              <option value="">Je suis…</option>
              <option>Une femme</option>
              <option>Un homme</option>
            </select>
          </div>
          <div class="form-group">
            <label>Je cherche</label>
            <select name="je_cherche">
              <option value="">Je cherche…</option>
              <option>Un homme</option>
              <option>Une femme</option>
              <option>Peu importe</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Ma ville</label>
            <input type="text" name="ville" placeholder="Ex : Paris, Lyon…" required />
          </div>
          <div class="form-group">
            <label>Date de naissance</label>
            <input type="date" name="date_naissance" max="2006-01-01" required />
          </div>
        </div>

        <div class="form-group">
          <label>Téléphone</label>
          <input type="tel" name="telephone" placeholder="+33 6 …" required />
        </div>

        <div class="form-group">
          <label>Centres d'intérêt <span style="font-size:0.75rem;color:#7A6E68;font-weight:400">(choisissez jusqu'à 5)</span></label>
          <div id="centres-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px;">
            <?php
            $interets = ['Voyage','Cuisine','Sport','Musique','Cinéma','Lecture','Yoga','Running',
                         'Randonnée','Photographie','Danse','Art','Nature','Jardinage','Gastronomie',
                         'Tennis','Natation','Théâtre','Méditation','Fitness'];
            foreach ($interets as $interet):
            ?>
            <label style="display:flex;align-items:center;gap:8px;padding:7px 10px;border:1.5px solid #EDE5D8;border-radius:8px;cursor:pointer;font-size:0.82rem;color:#3A2218;transition:all .2s;" 
                   onmouseover="this.style.borderColor='#8B1A1A'" 
                   onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='#EDE5D8'">
              <input type="checkbox" name="centres_interet[]" value="<?= $interet ?>" 
                     style="accent-color:#8B1A1A;" 
                     onchange="limitCheckbox(this)"/>
              <?= $interet ?>
            </label>
            <?php endforeach; ?>
          </div>
          <div id="centres-msg" style="font-size:0.75rem;color:#7A6E68;margin-top:6px;">0 / 5 sélectionnés</div>
        </div>

        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="mot_de_passe" id="mdp" placeholder="Créez votre mot de passe" required oninput="checkMdp()"/>
          <!-- Barre de force -->
          <div id="mdp-bar-wrap" style="display:none;margin-top:8px;">
            <div style="height:6px;background:#EDE5D8;border-radius:10px;overflow:hidden;">
              <div id="mdp-bar" style="height:100%;width:0%;border-radius:10px;transition:all .3s;"></div>
            </div>
            <div id="mdp-label" style="font-size:0.75rem;margin-top:4px;font-weight:500;"></div>
          </div>
          <!-- Critères -->
          <div id="mdp-criteres" style="display:none;margin-top:10px;background:#FAF6F0;border-radius:10px;padding:10px 14px;">
            <div class="critere" id="c-len">  ✗ Au moins 8 caractères</div>
            <div class="critere" id="c-maj">  ✗ Au moins une majuscule (A-Z)</div>
            <div class="critere" id="c-chif"> ✗ Au moins un chiffre (0-9)</div>
            <div class="critere" id="c-spec"> ✗ Au moins un caractère spécial (!@#$...)</div>
          </div>
        </div>

        <div class="form-group">
          <label>Confirmer mot de passe</label>
          <input type="password" name="confirm_mdp" id="confirm-mdp" placeholder="Répétez le mot de passe" required oninput="checkConfirm()"/>
          <div id="confirm-msg" style="font-size:0.78rem;margin-top:5px;"></div>
        </div>

      </div><!-- fin hack -->
      <p class="card-legal">
        En vous inscrivant, vous acceptez nos <a href="#">CGU</a> et notre <a href="#">politique de confidentialité</a>.
      </p>
    </div>
  </div>
</section>

<!-- STATS -->
<div class="stats-bar">
  <div class="stat reveal">
    <div class="stat-num">480 K+</div>
    <div class="stat-label">membres actifs</div>
  </div>
  <div class="stat reveal">
    <div class="stat-num">12 000+</div>
    <div class="stat-label">couples formés</div>
  </div>
  <div class="stat reveal">
    <div class="stat-num">96 %</div>
    <div class="stat-label">profils vérifiés</div>
  </div>
  <div class="stat reveal">
    <div class="stat-num">4.8★</div>
    <div class="stat-label">note moyenne</div>
  </div>
</div>

<!-- HOW IT WORKS -->
<section class="section" id="comment">
  <div style="max-width:1100px;margin:0 auto;">
    <p class="section-label reveal">Comment ça marche</p>
    <h2 class="section-title reveal">Trois étapes vers votre rencontre</h2>
    <p class="section-sub reveal">Simple, rapide et sécurisé — nous avons tout pensé pour faciliter vos rencontres.</p>

    <div class="steps-grid">
      <div class="step-card reveal" data-num="1">
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h3 class="step-title">Créez votre profil</h3>
        <p class="step-desc">En quelques minutes, renseignez vos informations et laissez votre numéro de téléphone. C'est gratuit et sans engagement.</p>
      </div>
      <div class="step-card reveal" data-num="2">
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </div>
        <h3 class="step-title">Découvrez des profils</h3>
        <p class="step-desc">Parcourez les profils de célibataires près de chez vous et repérez vos coups de cœur parmi notre communauté.</p>
      </div>
      <div class="step-card reveal" data-num="3">
        <div class="step-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.62 3.38 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82a16 16 0 0 0 6.29 6.29l.95-.95a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </div>
        <h3 class="step-title">Nous vous rappelons</h3>
        <p class="step-desc">Notre équipe vous contacte personnellement sous 24h pour vous mettre en relation avec le profil de votre choix.</p>
      </div>
    </div>
  </div>
</section>

<!-- PROFILES -->
<section class="profiles-section" id="profils">
  <div style="max-width:1100px;margin:0 auto;">
    <p class="section-label reveal">Profils populaires</p>
    <h2 class="section-title reveal">Faites leur connaissance</h2>
    <p class="section-sub reveal">Des personnes sincères qui, comme vous, cherchent une belle histoire.</p>

    <div class="profiles-grid">

      <div class="profile-card reveal">
        <img src="https://images.unsplash.com/photo-1508214751196-bcfd4ca60f91?w=400&q=80" alt="Sarah" class="profile-img"/>
        <div class="profile-info">
          <div class="profile-name">Sarah</div>
          <div class="profile-age">34 ans · Lyon</div>
          <div class="profile-tags">
            <span class="tag">Voyages</span>
            <span class="tag">Cuisine</span>
            <span class="tag">Yoga</span>
          </div>
        </div>
        <a href="#" class="profile-btn">Voir le profil</a>
      </div>

      <div class="profile-card reveal">
        <img src="https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=400&q=80" alt="Élodie" class="profile-img"/>
        <div class="profile-info">
          <div class="profile-name">Élodie</div>
          <div class="profile-age">29 ans · Paris</div>
          <div class="profile-tags">
            <span class="tag">Musique</span>
            <span class="tag">Cinéma</span>
            <span class="tag">Running</span>
          </div>
        </div>
        <a href="#" class="profile-btn">Voir le profil</a>
      </div>

      <div class="profile-card reveal">
        <img src="https://images.unsplash.com/photo-1589156215264-c8a1b13f5c6a?w=400&q=80" alt="Clarisse" class="profile-img"/>
        <div class="profile-info">
          <div class="profile-name">Clarisse</div>
          <div class="profile-age">35 ans · Bordeaux</div>
          <div class="profile-tags">
            <span class="tag">Nature</span>
            <span class="tag">Lecture</span>
            <span class="tag">Art</span>
          </div>
        </div>
        <a href="#" class="profile-btn">Voir le profil</a>
      </div>

      <div class="profile-card reveal">
        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&q=80" alt="Lydia" class="profile-img"/>
        <div class="profile-info">
          <div class="profile-name">Lydia</div>
          <div class="profile-age">46 ans · Toulouse</div>
          <div class="profile-tags">
            <span class="tag">Jardinage</span>
            <span class="tag">Gastronomie</span>
            <span class="tag">Randonnée</span>
          </div>
        </div>
        <a href="#" class="profile-btn">Voir le profil</a>
      </div>

    </div>
  </div>
</section>

<!-- SECURITY -->
<section class="security-section" id="securite">
  <div class="security-inner">
    <div>
      <p class="section-label reveal">Sécurité & Confiance</p>
      <h2 class="section-title reveal">Votre sécurité, notre priorité absolue</h2>
      <p class="section-sub reveal" style="margin-bottom:32px;">
        Nous mettons tout en œuvre pour que chaque rencontre sur Vie à deux soit une expérience positive, sereine et sécurisée.
      </p>
      <a href="#" style="display:inline-flex;align-items:center;gap:8px;background:var(--rouge);color:#fff;padding:13px 28px;border-radius:12px;text-decoration:none;font-size:0.9rem;font-weight:500;transition:all .25s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 24px rgba(139,26,26,0.35)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        En savoir plus sur la sécurité →
      </a>
    </div>

    <div class="security-visual reveal">
      <div class="security-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <div class="security-item-text">
          <h4>Profils vérifiés</h4>
          <p>Chaque profil passe par une validation manuelle avant publication. Zéro fake toléré.</p>
        </div>
      </div>
      <div class="security-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <div class="security-item-text">
          <h4>Données chiffrées</h4>
          <p>Vos données personnelles sont protégées par un chiffrement SSL de bout en bout.</p>
        </div>
      </div>
      <div class="security-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <div class="security-item-text">
          <h4>Modération active</h4>
          <p>Une équipe dédiée surveille les échanges 7j/7 pour garantir un espace bienveillant.</p>
        </div>
      </div>
      <div class="security-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div class="security-item-text">
          <h4>Signalement facile</h4>
          <p>Un bouton de signalement accessible partout pour réagir en un clic.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="footer-top">
    <div>
      <div class="footer-logo">♡ Vie à deux</div>
      <p class="footer-desc">La rencontre sérieuse et bienveillante, pour tous ceux qui croient encore à l'amour vrai.</p>
    </div>
    <div class="footer-col">
      <h5>Navigation</h5>
      <a href="#comment">Comment ça marche</a>
      <a href="#profils">Profils populaires</a>
      <a href="#securite">Sécurité</a>
      <a href="connexion.php">Connexion</a>
    </div>
    <div class="footer-col">
      <h5>Mon compte</h5>
      <a href="index.html">S'inscrire gratuitement</a>
      <a href="connexion.php">Se connecter</a>
      <a href="mdp_oublie.php">Mot de passe oublié</a>
    </div>
    <div class="footer-col">
      <h5>Contact</h5>
      <a href="mailto:contact@vieadeux.fr">contact@vieadeux.fr</a>
    </div>
  </div>
  <div class="footer-bottom">
    © 2025 Vie à deux — Tous droits réservés &nbsp;|&nbsp; Site sécurisé 🔒 &nbsp;|&nbsp; Données personnelles protégées conformément au RGPD
  </div>
</footer>

<script>
function checkMdp() {
  const mdp = document.getElementById('mdp').value;
  const bar = document.getElementById('mdp-bar');
  const label = document.getElementById('mdp-label');
  const wrap = document.getElementById('mdp-bar-wrap');
  const criteres = document.getElementById('mdp-criteres');

  wrap.style.display = mdp.length > 0 ? '' : 'none';
  criteres.style.display = mdp.length > 0 ? '' : 'none';

  const len  = mdp.length >= 8;
  const maj  = /[A-Z]/.test(mdp);
  const chif = /[0-9]/.test(mdp);
  const spec = /[!@#$%^&*()_+\-=\[\]{};':"\|,.<>\/?]/.test(mdp);

  // Mise à jour critères
  setCritere('c-len',  len,  '✓ Au moins 8 caractères', '✗ Au moins 8 caractères');
  setCritere('c-maj',  maj,  '✓ Au moins une majuscule (A-Z)', '✗ Au moins une majuscule (A-Z)');
  setCritere('c-chif', chif, '✓ Au moins un chiffre (0-9)', '✗ Au moins un chiffre (0-9)');
  setCritere('c-spec', spec, '✓ Au moins un caractère spécial (!@#$...)', '✗ Au moins un caractère spécial (!@#$...)');

  // Force
  const score = [len, maj, chif, spec].filter(Boolean).length;
  const configs = [
    { w:'0%',   color:'#EDE5D8', txt:'' },
    { w:'25%',  color:'#e74c3c', txt:'Très faible 🔴' },
    { w:'50%',  color:'#e67e22', txt:'Faible 🟠' },
    { w:'75%',  color:'#f1c40f', txt:'Moyen 🟡' },
    { w:'100%', color:'#27ae60', txt:'Fort 🟢' },
  ];
  const c = configs[score];
  bar.style.width  = c.w;
  bar.style.background = c.color;
  label.textContent = c.txt;
  label.style.color = c.color;

  checkConfirm();
}

function setCritere(id, ok, txtOk, txtNon) {
  const el = document.getElementById(id);
  el.textContent = ok ? txtOk : txtNon;
  el.className = 'critere' + (ok ? ' ok' : '');
}

function checkConfirm() {
  const mdp     = document.getElementById('mdp').value;
  const confirm = document.getElementById('confirm-mdp').value;
  const msg     = document.getElementById('confirm-msg');
  if (!confirm) { msg.textContent = ''; return; }
  if (mdp === confirm) {
    msg.textContent = '✓ Les mots de passe correspondent';
    msg.style.color = '#27ae60';
  } else {
    msg.textContent = '✗ Les mots de passe ne correspondent pas';
    msg.style.color = '#e74c3c';
  }
}
</script>

<script>
  // Scroll reveal
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
      if (e.isIntersecting) {
        setTimeout(() => e.target.classList.add('visible'), i * 80);
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.12 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // Stat counter animation
  function animateCount(el, target, suffix = '') {
    let start = 0;
    const isFloat = target % 1 !== 0;
    const step = target / 60;
    const timer = setInterval(() => {
      start += step;
      if (start >= target) { start = target; clearInterval(timer); }
      el.textContent = (isFloat ? start.toFixed(1) : Math.floor(start).toLocaleString('fr-FR')) + suffix;
    }, 25);
  }
  const statObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const num = e.target.querySelector('.stat-num');
        const text = num.textContent;
        if (text.includes('480')) animateCount(num, 480, ' K+');
        else if (text.includes('12')) animateCount(num, 12000, '+');
        else if (text.includes('96')) animateCount(num, 96, ' %');
        else if (text.includes('4.8')) animateCount(num, 4.8, '★');
        statObs.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('.stat').forEach(el => statObs.observe(el));
</script>
<script>
function limitCheckbox(cb) {
  const checks = document.querySelectorAll('input[name="centres_interet[]"]');
  const checked = [...checks].filter(c => c.checked);
  const msg = document.getElementById('centres-msg');
  if (checked.length > 5) {
    cb.checked = false;
    msg.textContent = '5 / 5 — maximum atteint !';
    msg.style.color = '#8B1A1A';
    return;
  }
  msg.textContent = `${checked.length} / 5 sélectionné${checked.length > 1 ? 's' : ''}`;
  msg.style.color = checked.length === 5 ? '#4CAF50' : '#7A6E68';
  // Mettre en surbrillance les cases cochées
  checks.forEach(c => {
    c.parentElement.style.borderColor = c.checked ? '#8B1A1A' : '#EDE5D8';
    c.parentElement.style.background  = c.checked ? '#fce8e8' : '';
  });
}

</script>
</body>
</html>
