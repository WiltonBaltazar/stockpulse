<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockPulse | Gestao para Padaria e Salgados</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #685D94;
            --soft-1: #E0DDE9;
            --soft-2: #CEC7E5;
            --soft-3: #F0EFF4;
            --white: #FFFFFF;
            --black: #000000;
            --ink: #0f1020;
            --ink-soft: #181a32;
            --radius-xl: 1.6rem;
            --radius-lg: 1.05rem;
            --radius-md: .75rem;
            --shadow-soft: 0 18px 50px rgba(24, 17, 43, 0.13);
            --shadow-deep: 0 30px 70px rgba(16, 8, 35, 0.35);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            background: var(--soft-3);
            color: var(--black);
            font-family: "Manrope", sans-serif;
            scroll-behavior: smooth;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 8% 0%, rgba(104, 93, 148, .18), transparent 26%),
                radial-gradient(circle at 94% 8%, rgba(206, 199, 229, .4), transparent 28%);
            z-index: -1;
        }

        .container {
            width: min(1160px, calc(100% - 2rem));
            margin: 0 auto;
        }

        .header-wrap {
            position: sticky;
            top: .7rem;
            z-index: 30;
            padding-top: .7rem;
        }

        .header {
            background: rgba(255, 255, 255, .86);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(206, 199, 229, .7);
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .66rem .72rem .66rem 1rem;
            box-shadow: var(--shadow-soft);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: .64rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.03rem;
            font-weight: 700;
            color: #13142c;
            text-decoration: none;
        }

        .brand-dot {
            width: 1.85rem;
            height: 1.85rem;
            border-radius: .58rem;
            display: inline-grid;
            place-items: center;
            color: var(--white);
            font-size: .72rem;
            font-weight: 700;
            background: linear-gradient(140deg, #4f4674, #7d6fba);
            box-shadow: 0 12px 20px rgba(82, 71, 124, .4);
        }

        .menu {
            display: inline-flex;
            align-items: center;
            gap: 1.25rem;
        }

        .menu a {
            text-decoration: none;
            color: rgba(16, 16, 26, .76);
            font-size: .88rem;
            font-weight: 700;
        }

        .powered-inline {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid #d7d0eb;
            background: #fff;
            border-radius: 999px;
            padding: .24rem .5rem .24rem .3rem;
        }

        .powered-inline span {
            font-size: .68rem;
            font-weight: 700;
            color: rgba(21, 17, 36, .72);
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .powered-inline img {
            height: 1rem;
            width: auto;
            display: block;
        }

        .header-actions {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
        }

        .btn {
            border: 0;
            text-decoration: none;
            border-radius: 999px;
            padding: .68rem 1.03rem;
            font-weight: 700;
            font-size: .84rem;
            transition: transform .2s ease, box-shadow .2s ease;
            cursor: pointer;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-light {
            background: #ffffff;
            border: 1px solid #d7d0eb;
            color: #2f2a48;
        }

        .btn-dark {
            background: #0f1020;
            color: #ffffff;
            box-shadow: 0 10px 20px rgba(12, 14, 28, .28);
        }

        .hero {
            padding: 2.4rem 0 2.1rem;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            gap: 1.4rem;
            align-items: center;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            color: #43396c;
            border: 1px solid #cfc7e5;
            background: rgba(255, 255, 255, .82);
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
            letter-spacing: .02em;
            padding: .35rem .72rem;
        }

        .hero h1 {
            font-family: "Space Grotesk", sans-serif;
            margin: .84rem 0 .4rem;
            font-size: clamp(2rem, 6vw, 3.2rem);
            line-height: 1.02;
            letter-spacing: -.02em;
            color: #111327;
            max-width: 14ch;
        }

        .hero p {
            margin: 0;
            max-width: 49ch;
            font-size: 1rem;
            line-height: 1.47;
            color: rgba(15, 17, 35, .78);
        }

        .hero-cta {
            margin-top: 1.18rem;
            display: flex;
            flex-wrap: wrap;
            gap: .7rem;
        }

        .app-badges {
            margin-top: 1rem;
            display: inline-flex;
            gap: .55rem;
            flex-wrap: wrap;
        }

        .badge {
            border-radius: .62rem;
            border: 1px solid #201a35;
            background: #0e101f;
            color: #fff;
            padding: .5rem .65rem;
            font-size: .72rem;
            line-height: 1.2;
            min-width: 7.9rem;
            font-weight: 600;
        }

        .badge strong {
            display: block;
            font-size: .82rem;
            font-weight: 700;
        }

        .hero-mosaic {
            min-height: 430px;
            border-radius: var(--radius-xl);
            background:
                linear-gradient(160deg, rgba(255, 255, 255, .96), rgba(240, 239, 244, .9));
            border: 1px solid rgba(206, 199, 229, .9);
            box-shadow: var(--shadow-soft);
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: .62rem;
            padding: .68rem;
        }

        .mosaic-card {
            border-radius: 1rem;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: end;
            min-height: 100%;
            color: #fff;
        }

        .mosaic-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(to top, rgba(14, 12, 31, .86), rgba(14, 12, 31, .08) 55%);
            z-index: 1;
        }

        .mosaic-card img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: saturate(1.02) contrast(1.01);
        }

        .mosaic-content {
            position: relative;
            z-index: 2;
            padding: .95rem;
            width: 100%;
        }

        .mosaic-content h3 {
            margin: 0;
            font-size: .96rem;
            font-family: "Space Grotesk", sans-serif;
            line-height: 1.2;
        }

        .mosaic-content p {
            margin: .26rem 0 0;
            font-size: .74rem;
            color: rgba(255, 255, 255, .88);
            line-height: 1.3;
        }

        .band {
            margin-top: .3rem;
            background: linear-gradient(140deg, #5e4daa, #685D94 45%, #7a6fbe);
            color: #fff;
            border-radius: 0 0 3.3rem 3.3rem;
            padding: 2.1rem 0;
            text-align: center;
            box-shadow: var(--shadow-soft);
        }

        .band h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.24rem, 3vw, 1.68rem);
        }

        .band p {
            margin: .45rem auto 0;
            max-width: 64ch;
            font-size: .95rem;
            color: rgba(255, 255, 255, .9);
        }

        .testimonials {
            margin-top: 1.2rem;
            background:
                radial-gradient(circle at 10% 0%, rgba(104, 93, 148, .26), transparent 34%),
                linear-gradient(165deg, var(--ink), #0b0c19);
            color: #fff;
            border-radius: var(--radius-xl);
            padding: 2rem 1.2rem 1.4rem;
            box-shadow: var(--shadow-deep);
        }

        .section-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
        }

        .section-head h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.35rem, 3vw, 1.95rem);
        }

        .section-head p {
            margin: .34rem 0 0;
            color: rgba(255, 255, 255, .75);
            max-width: 54ch;
            font-size: .92rem;
        }

        .quote-circle {
            width: 5.7rem;
            height: 5.7rem;
            border-radius: 50%;
            border: 1px dashed rgba(255, 255, 255, .4);
            display: grid;
            place-items: center;
            font-family: "Space Grotesk", sans-serif;
            font-size: 2.1rem;
            color: rgba(255, 255, 255, .9);
            flex: 0 0 auto;
        }

        .testi-grid {
            margin-top: 1.1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .8rem;
        }

        .testi {
            border-radius: .92rem;
            border: 1px solid rgba(255, 255, 255, .14);
            background: rgba(255, 255, 255, .08);
            padding: .86rem;
        }

        .testi h3 {
            margin: 0;
            font-size: .93rem;
            font-family: "Space Grotesk", sans-serif;
        }

        .testi small {
            color: #bfb6dd;
            font-weight: 700;
            font-size: .74rem;
        }

        .testi p {
            margin: .52rem 0 0;
            font-size: .83rem;
            line-height: 1.4;
            color: rgba(255, 255, 255, .85);
        }

        .features {
            margin-top: 1.5rem;
            border: 1px solid rgba(206, 199, 229, .85);
            border-radius: var(--radius-xl);
            background: #fff;
            padding: 1.6rem 1rem 1rem;
            box-shadow: var(--shadow-soft);
        }

        .features h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.35rem, 3vw, 1.92rem);
            text-align: center;
        }

        .features p.lead {
            margin: .4rem auto 0;
            max-width: 64ch;
            text-align: center;
            color: rgba(15, 17, 35, .72);
            font-size: .95rem;
        }

        .feature-stage {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: .65rem;
            align-items: end;
        }

        .shot {
            border-radius: .95rem;
            border: 1px solid #e0ddea;
            overflow: hidden;
            background: #f4f2f9;
        }

        .shot img {
            display: block;
            width: 100%;
            height: auto;
        }

        .shot.main {
            transform: translateY(-.4rem);
            box-shadow: 0 20px 40px rgba(35, 21, 71, .2);
            border-color: #c4bcde;
        }

        .feature-grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .7rem;
        }

        .feature-card {
            border: 1px solid #e1dceb;
            border-radius: .8rem;
            padding: .76rem;
            background: #fff;
        }

        .feature-card h3 {
            margin: 0;
            font-size: .91rem;
            font-family: "Space Grotesk", sans-serif;
            color: #1f1836;
        }

        .feature-card p {
            margin: .28rem 0 0;
            font-size: .82rem;
            color: rgba(20, 18, 37, .7);
            line-height: 1.35;
        }

        .cta {
            margin-top: 1.5rem;
            border-radius: var(--radius-xl);
            overflow: hidden;
            position: relative;
            min-height: 260px;
            color: #fff;
            box-shadow: var(--shadow-soft);
        }

        .cta::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(100deg, rgba(8, 10, 20, .85), rgba(8, 10, 20, .3)),
                url('{{ asset('images/stockpulse-financial-control-demo.png') }}') center/cover no-repeat;
            filter: saturate(.85) blur(.2px);
            transform: scale(1.02);
        }

        .cta-content {
            position: relative;
            z-index: 2;
            padding: 2rem 1.2rem;
            max-width: 35rem;
        }

        .cta h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.5rem, 3.2vw, 2.2rem);
            line-height: 1.05;
        }

        .cta p {
            margin: .45rem 0 0;
            color: rgba(255, 255, 255, .86);
            line-height: 1.45;
        }

        .pricing {
            margin-top: 1.5rem;
            background: #fff;
            border: 1px solid #e0dbe9;
            border-radius: var(--radius-xl);
            padding: 1.4rem .95rem;
            box-shadow: var(--shadow-soft);
        }

        .pricing h2 {
            margin: 0;
            text-align: center;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.35rem, 3vw, 1.94rem);
        }

        .pricing p {
            margin: .38rem auto 0;
            max-width: 52ch;
            text-align: center;
            color: rgba(16, 18, 34, .72);
        }

        .price-card {
            margin: .95rem auto 0;
            width: min(560px, 100%);
            border: 1px solid #ddd6ec;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
        }

        .price-head {
            text-align: center;
            padding: 1.18rem .9rem;
            color: #fff;
            background: linear-gradient(145deg, #5f4dac, #685D94 52%, #7b71be);
        }

        .price-head h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.24rem;
        }

        .price-head strong {
            display: block;
            margin-top: .24rem;
            font-size: 1.95rem;
            font-family: "Space Grotesk", sans-serif;
        }

        .price-body {
            padding: .95rem;
        }

        .price-line {
            border: 1px solid #cbc3e5;
            border-radius: 999px;
            text-align: center;
            padding: .5rem .7rem;
            font-size: .84rem;
            font-weight: 700;
            color: #2c2449;
            background: #f9f7ff;
        }

        .checklist {
            margin: .78rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: .45rem;
            font-size: .86rem;
        }

        .checklist li {
            display: flex;
            gap: .45rem;
            align-items: start;
            color: rgba(20, 16, 38, .82);
        }

        .checklist li::before {
            content: "";
            width: .58rem;
            height: .58rem;
            border-radius: 50%;
            margin-top: .28rem;
            background: linear-gradient(135deg, #5e4dac, #887cbd);
            flex: 0 0 auto;
        }

        .faq {
            margin-top: 1.6rem;
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 1rem;
            align-items: center;
        }

        .faq-avatar {
            width: 210px;
            height: 210px;
            border-radius: 50%;
            margin-inline: auto;
            background:
                radial-gradient(circle at 35% 22%, #8e81c9, #685D94 45%, #4b3f72);
            color: #fff;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 1rem;
            box-shadow: 0 18px 40px rgba(66, 55, 104, .35);
        }

        .faq-avatar strong {
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.35rem;
            line-height: 1.2;
            display: block;
        }

        .faq-panel {
            background: #fff;
            border: 1px solid #ddd6ec;
            border-radius: 1rem;
            padding: .8rem;
            box-shadow: var(--shadow-soft);
        }

        .faq-panel h2 {
            margin: 0 0 .6rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.46rem;
        }

        .faq-item {
            border: 1px dashed #c9bfeb;
            border-radius: .8rem;
            background: #fdfcff;
        }

        .faq-item + .faq-item {
            margin-top: .5rem;
        }

        .faq-button {
            appearance: none;
            border: 0;
            background: transparent;
            width: 100%;
            text-align: left;
            padding: .78rem .82rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .7rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: .95rem;
            cursor: pointer;
        }

        .faq-icon {
            width: 1.6rem;
            height: 1.6rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: #685D94;
            color: #fff;
            font-weight: 700;
            line-height: 1;
            font-size: 1rem;
            flex: 0 0 auto;
        }

        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height .3s ease;
            padding: 0 .82rem;
            color: rgba(25, 18, 42, .76);
            font-size: .88rem;
            line-height: 1.45;
        }

        .faq-item.active .faq-content {
            max-height: 220px;
            padding: 0 .82rem .72rem;
        }

        .faq-item.active .faq-icon {
            background: #0f1020;
        }

        .signup {
            margin-top: 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: .9rem;
        }

        .signup-info {
            background: #0f1020;
            color: #fff;
            border-radius: 1rem;
            padding: 1rem;
        }

        .signup-info h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.55rem;
            line-height: 1.1;
        }

        .signup-info p {
            margin: .46rem 0 0;
            color: rgba(255, 255, 255, .84);
            line-height: 1.45;
            font-size: .92rem;
        }

        .signup-links {
            margin-top: .9rem;
            display: grid;
            gap: .4rem;
        }

        .signup-links a {
            color: #d7d0f2;
            font-size: .86rem;
            font-weight: 700;
            text-decoration: none;
        }

        .signup-card {
            background: #fff;
            border: 1px solid #ddd6ec;
            border-radius: 1rem;
            padding: .95rem;
            box-shadow: var(--shadow-soft);
        }

        .signup-card h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.34rem;
        }

        .signup-card p {
            margin: .32rem 0 0;
            color: rgba(20, 17, 36, .7);
            font-size: .9rem;
        }

        .errors {
            margin: .72rem 0 0;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #991b1b;
            border-radius: .7rem;
            padding: .58rem .65rem;
            font-size: .84rem;
            font-weight: 700;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .65rem;
        }

        .field {
            margin-top: .62rem;
        }

        label {
            display: block;
            margin-bottom: .3rem;
            font-size: .83rem;
            font-weight: 700;
            color: #211a38;
        }

        input {
            width: 100%;
            border: 1px solid #d7cfea;
            border-radius: .7rem;
            padding: .68rem .75rem;
            font: inherit;
            color: #161127;
            background: #fff;
            transition: box-shadow .2s ease, border-color .2s ease;
        }

        input:focus {
            outline: none;
            border-color: #685D94;
            box-shadow: 0 0 0 3px rgba(104, 93, 148, .2);
        }

        .submit {
            margin-top: .82rem;
            width: 100%;
            border: 0;
            border-radius: .72rem;
            padding: .76rem 1rem;
            font: 700 .92rem "Manrope", sans-serif;
            color: #fff;
            background: linear-gradient(140deg, #5f4dac, #685D94 55%, #7f74c2);
            cursor: pointer;
        }

        .help {
            margin-top: .54rem;
            font-size: .8rem;
            color: rgba(20, 17, 38, .66);
        }

        .footer {
            margin: 1.2rem 0 1.7rem;
            text-align: center;
            color: rgba(23, 20, 41, .62);
            font-size: .84rem;
            font-weight: 700;
        }

        @media (max-width: 1080px) {
            .menu {
                display: none;
            }

            .hero {
                grid-template-columns: 1fr;
            }

            .hero-mosaic {
                min-height: 340px;
            }

            .testi-grid {
                grid-template-columns: 1fr;
            }

            .feature-stage {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .feature-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .faq {
                grid-template-columns: 1fr;
            }

            .signup {
                grid-template-columns: 1fr;
            }

            .powered-inline span {
                display: none;
            }
        }

        @media (max-width: 760px) {
            .container {
                width: min(1160px, calc(100% - 1rem));
            }

            .header {
                border-radius: 1rem;
                padding: .75rem;
            }

            .header-actions .btn {
                padding: .58rem .8rem;
            }

            .powered-inline {
                display: none;
            }

            .hero {
                padding-top: 1.2rem;
            }

            .hero-mosaic {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .mosaic-card {
                min-height: 150px;
            }

            .band {
                border-radius: 0 0 2.1rem 2.1rem;
                padding: 1.5rem 0;
            }

            .feature-stage {
                grid-template-columns: 1fr 1fr;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header-wrap">
        <div class="container">
            <header class="header">
                <a href="{{ route('landing') }}" class="brand">
                    <span class="brand-dot">SP</span>
                    <span>StockPulse</span>
                </a>

                <nav class="menu">
                    <a href="#funcionalidades">Funcionalidades</a>
                    <a href="#depoimentos">Depoimentos</a>
                    <a href="#planos">Planos</a>
                    <a href="#faq">FAQ</a>
                </nav>

                <div class="header-actions">
                    <div class="powered-inline" aria-label="Powered by Cheesemania">
                        <span>Powered by</span>
                        <img src="{{ asset('images/cheesemania.png') }}" alt="Cheesemania">
                    </div>
                    <a class="btn btn-light" href="{{ url('/admin/login') }}">Entrar</a>
                    <a class="btn btn-dark" href="#cadastro">Criar conta</a>
                </div>
            </header>
        </div>
    </div>

    <main class="container">
        <section class="hero">
            <div>
                <span class="kicker">Feito para padaria, confeitaria e salgados</span>
                <h1>Gestao completa da sua producao e vendas.</h1>
                <p>Controle custos, perdas, compras, vendas e estoque no mesmo lugar. Veja lucro real sem planilhas confusas.</p>

                <div class="hero-cta">
                    <a class="btn btn-dark" href="#cadastro">Comecar agora</a>
                    <a class="btn btn-light" href="{{ url('/admin/login') }}">Ver painel</a>
                </div>

                <div class="app-badges">
                    <div class="badge">
                        <strong>Registo de Vendas</strong>
                        Offline e online
                    </div>
                    <div class="badge">
                        <strong>Controlo Financeiro</strong>
                        Receitas e despesas
                    </div>
                </div>
            </div>

            <aside class="hero-mosaic">
                <article class="mosaic-card">
                    <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Painel financeiro do StockPulse" loading="lazy">
                    <div class="mosaic-content">
                        <h3>Controlo financeiro claro</h3>
                        <p>Receitas, despesas, ganhos e pendentes em tempo real.</p>
                    </div>
                </article>

                <article class="mosaic-card">
                    <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Relatorios de vendas" loading="lazy">
                    <div class="mosaic-content">
                        <h3>Vendas</h3>
                        <p>Registo automatico de referencia.</p>
                    </div>
                </article>

                <article class="mosaic-card">
                    <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Custos de producao e estoque" loading="lazy">
                    <div class="mosaic-content">
                        <h3>Producao</h3>
                        <p>Custo por lote e disponibilidade.</p>
                    </div>
                </article>
            </aside>
        </section>
    </main>

    <section class="band">
        <div class="container">
            <h2>StockPulse e construido para quem vive da producao diaria.</h2>
            <p>Tome decisoes com base em dados: o que vender, quanto produzir, quando comprar e onde esta o lucro.</p>
        </div>
    </section>

    <section id="depoimentos" class="container testimonials">
        <div class="section-head">
            <div>
                <h2>Mais de 4.000 negocios alimentares acompanhados</h2>
                <p>Padarias e cozinhas de salgados usam o StockPulse para reduzir perdas, acelerar vendas e melhorar margem.</p>
            </div>
            <div class="quote-circle">"</div>
        </div>

        <div class="testi-grid">
            <article class="testi">
                <h3>Rogeria - Padaria Centro</h3>
                <small>Beira</small>
                <p>"Hoje sei exatamente quanto ganho por venda. Antes eu vendia muito, mas nao sabia meu resultado real."</p>
            </article>
            <article class="testi">
                <h3>Alex - Salgados da Casa</h3>
                <small>Maputo</small>
                <p>"Passei a registrar vendas offline e compras no mesmo dia. O caixa ficou muito mais previsivel."</p>
            </article>
            <article class="testi">
                <h3>Mara - Forno do Bairro</h3>
                <small>Nampula</small>
                <p>"Com o controlo de estoque por receita, paro de produzir no escuro. Reduzi desperdicio em poucas semanas."</p>
            </article>
        </div>
    </section>

    <section id="funcionalidades" class="container features">
        <h2>Tudo que voce precisa para operar com confianca</h2>
        <p class="lead">Da receita ao financeiro: fluxo completo para vender melhor e proteger sua margem.</p>

        <div class="feature-stage">
            <figure class="shot"><img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela do StockPulse 1" loading="lazy"></figure>
            <figure class="shot"><img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela do StockPulse 2" loading="lazy"></figure>
            <figure class="shot main"><img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela principal do StockPulse" loading="lazy"></figure>
            <figure class="shot"><img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela do StockPulse 3" loading="lazy"></figure>
            <figure class="shot"><img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela do StockPulse 4" loading="lazy"></figure>
        </div>

        <div class="feature-grid">
            <article class="feature-card">
                <h3>Receitas e custo por lote</h3>
                <p>Calcule custo de producao por unidade com ingredientes, embalagem e custos indiretos.</p>
            </article>
            <article class="feature-card">
                <h3>Vendas offline e online</h3>
                <p>Registre qualquer venda e acompanhe o movimento total de dinheiro.</p>
            </article>
            <article class="feature-card">
                <h3>Referencias automaticas</h3>
                <p>Cada venda e transacao recebe referencia automatica para rastreio.</p>
            </article>
            <article class="feature-card">
                <h3>Alertas visuais de estoque</h3>
                <p>Indicacao clara quando e possivel produzir ou vender, e quando falta insumo.</p>
            </article>
            <article class="feature-card">
                <h3>Painel financeiro completo</h3>
                <p>Veja receitas, despesas, perdas, compras e ganho liquido por periodo.</p>
            </article>
            <article class="feature-card">
                <h3>Padrao de valores monetarios</h3>
                <p>Valores exibidos em formato legivel: 1.000,00 MT em toda a aplicacao.</p>
            </article>
        </div>
    </section>

    <section class="container cta">
        <div class="cta-content">
            <h2>Pare de decidir no escuro.</h2>
            <p>Com o StockPulse voce enxerga o que entra, o que sai e o que realmente sobra no fim do dia.</p>
            <div class="hero-cta">
                <a class="btn btn-dark" href="#cadastro">Criar conta agora</a>
            </div>
        </div>
    </section>

    <section id="planos" class="container pricing">
        <h2>Plano simples, valor claro</h2>
        <p>Sem complicacao. Tudo para sua operacao em um unico plano mensal.</p>

        <article class="price-card">
            <header class="price-head">
                <h3>Acesso completo</h3>
                <strong>14,90 MT</strong>
                <span>por mes</span>
            </header>
            <div class="price-body">
                <div class="price-line">Sem limite de registros de vendas e transacoes</div>
                <ul class="checklist">
                    <li>Controle de receitas, ingredientes e estoque</li>
                    <li>Vendas offline e online com referencia automatica</li>
                    <li>Painel financeiro com filtros por periodo</li>
                    <li>Indicadores de perdas, compras e ganho real</li>
                    <li>Suporte para operacao de padaria e salgados</li>
                </ul>
            </div>
        </article>
    </section>

    <section id="faq" class="container faq">
        <div class="faq-avatar">
            <div>
                <strong>Duvidas?</strong>
                <span>Respondemos aqui.</span>
            </div>
        </div>

        <div class="faq-panel">
            <h2>Perguntas frequentes</h2>

            <article class="faq-item active">
                <button class="faq-button" type="button">
                    <span>Posso registrar venda sem receita?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">
                    Sim. Voce pode registrar venda avulsa e ainda acompanhar o valor movimentado no financeiro.
                </div>
            </article>

            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>Consigo ver perdas e quebras?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">
                    Sim. Ha categoria especifica para perdas, com impacto direto no painel financeiro.
                </div>
            </article>

            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>Serve para padaria e salgados?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">
                    Sim. O fluxo foi estruturado para quem produz por receita, controla insumos e vende unidades.
                </div>
            </article>

            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>O cadastro ja da acesso ao painel?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">
                    Sim. A conta e criada e voce entra automaticamente no painel administrativo.
                </div>
            </article>
        </div>
    </section>

    <section id="cadastro" class="container signup">
        <aside class="signup-info">
            <h2>Comece hoje com o StockPulse</h2>
            <p>Crie sua conta, entre no painel e comece a registrar producao, vendas e financeiro no mesmo dia.</p>

            <div style="margin-top: .9rem;">
                <img src="{{ asset('images/cheesemania.png') }}" alt="Cheesemania" style="height: 1.4rem; width: auto;">
            </div>

            <div class="signup-links">
                <a href="{{ url('/admin/login') }}">Ja tem conta? Entrar</a>
                <a href="{{ route('landing') }}">Voltar ao topo</a>
            </div>
        </aside>

        <article class="signup-card">
            <h3>Criar conta</h3>
            <p>Preencha os dados para aceder imediatamente.</p>

            @if ($errors->any())
                <div class="errors">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('landing.signup') }}">
                @csrf

                <div class="grid-2">
                    <div class="field">
                        <label for="name">Nome</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="field">
                    <label for="contact_number">Numero de contacto</label>
                    <input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number') }}" placeholder="+258 84 123 4567" required>
                </div>

                <div class="grid-2">
                    <div class="field">
                        <label for="password">Senha</label>
                        <input id="password" name="password" type="password" minlength="8" required>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Confirmar senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
                    </div>
                </div>

                <button class="submit" type="submit">Criar conta e entrar no painel</button>
            </form>

            <div class="help">Ao cadastrar, voce entra direto no dashboard.</div>
        </article>
    </section>

    <footer class="footer container">Powered by Cheesemania</footer>

    <script>
        (() => {
            const input = document.getElementById('contact_number');
            if (input) {
                const formatMozNumber = (rawValue) => {
                    let digits = String(rawValue || '').replace(/\D/g, '');

                    if (digits.startsWith('258')) {
                        digits = digits.slice(3);
                    }

                    digits = digits.slice(0, 9);

                    const a = digits.slice(0, 2);
                    const b = digits.slice(2, 5);
                    const c = digits.slice(5, 9);

                    let formatted = '+258';
                    if (a) formatted += ` ${a}`;
                    if (b) formatted += ` ${b}`;
                    if (c) formatted += ` ${c}`;

                    return formatted;
                };

                input.addEventListener('focus', () => {
                    if (!input.value.trim()) {
                        input.value = '+258 ';
                    }
                });

                input.addEventListener('input', () => {
                    input.value = formatMozNumber(input.value);
                });

                if (input.value) {
                    input.value = formatMozNumber(input.value);
                }
            }

            const items = Array.from(document.querySelectorAll('.faq-item'));
            items.forEach((item) => {
                const button = item.querySelector('.faq-button');
                if (!button) return;

                button.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    items.forEach((entry) => entry.classList.remove('active'));
                    if (!isActive) {
                        item.classList.add('active');
                    }
                });
            });
        })();
    </script>
</body>
</html>
