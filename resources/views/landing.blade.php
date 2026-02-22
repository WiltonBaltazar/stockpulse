<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockPulse | Sistema Completo para Confeitaria</title>
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
            --ink-soft: #15182b;
            --muted: rgba(0, 0, 0, 0.68);
            --radius-xl: 1.55rem;
            --radius-lg: 1rem;
            --radius-md: .75rem;
            --shadow-soft: 0 18px 45px rgba(28, 20, 50, 0.13);
            --shadow-deep: 0 26px 60px rgba(15, 12, 32, 0.35);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            font-family: "Manrope", sans-serif;
            color: var(--black);
            background: var(--soft-3);
            scroll-behavior: smooth;
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(circle at 10% -5%, rgba(104, 93, 148, .19), transparent 28%),
                radial-gradient(circle at 95% 6%, rgba(206, 199, 229, .42), transparent 30%),
                linear-gradient(rgba(104, 93, 148, .03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(206, 199, 229, .08) 1px, transparent 1px);
            background-size: auto, auto, 20px 20px, 20px 20px;
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
            background: rgba(255, 255, 255, .9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(206, 199, 229, .86);
            border-radius: 999px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: .66rem .72rem .66rem 1rem;
            box-shadow: var(--shadow-soft);
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: .62rem;
            font-family: "Space Grotesk", sans-serif;
            font-weight: 700;
            color: #17182e;
            text-decoration: none;
            font-size: 1.02rem;
        }

        .brand-dot {
            width: 1.82rem;
            height: 1.82rem;
            border-radius: .56rem;
            display: inline-grid;
            place-items: center;
            color: #fff;
            font-size: .72rem;
            font-weight: 700;
            background: linear-gradient(140deg, #4f4771, var(--primary));
            box-shadow: 0 10px 18px rgba(92, 78, 140, .36);
        }

        .menu {
            display: inline-flex;
            align-items: center;
            gap: 1.2rem;
        }

        .menu a {
            text-decoration: none;
            color: rgba(20, 20, 34, .75);
            font-size: .87rem;
            font-weight: 700;
        }

        .header-actions {
            display: inline-flex;
            align-items: center;
            gap: .56rem;
        }

        .powered-inline {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border: 1px solid #d8d2ea;
            background: #fff;
            border-radius: 999px;
            padding: .24rem .52rem .24rem .32rem;
        }

        .powered-inline span {
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: rgba(22, 20, 37, .7);
        }

        .powered-inline img {
            display: block;
            width: auto;
            height: 1rem;
        }

        .btn {
            border: 0;
            text-decoration: none;
            border-radius: 999px;
            padding: .68rem 1rem;
            font-size: .84rem;
            font-weight: 700;
            transition: transform .2s ease;
            cursor: pointer;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-light {
            background: #fff;
            border: 1px solid #dad3ed;
            color: #322b4f;
        }

        .btn-dark {
            background: #101225;
            color: #fff;
            box-shadow: 0 10px 20px rgba(16, 18, 37, .25);
        }

        .btn-main {
            background: linear-gradient(140deg, #5f5488, var(--primary));
            color: #fff;
            box-shadow: 0 10px 22px rgba(94, 83, 140, .34);
        }

        .hero {
            padding: 2.4rem 0 1.8rem;
            display: grid;
            grid-template-columns: 1fr 1.12fr;
            gap: 1.35rem;
            align-items: center;
        }

        .kicker {
            display: inline-flex;
            border-radius: 999px;
            border: 1px solid #cdc6e5;
            background: rgba(255, 255, 255, .88);
            padding: .35rem .74rem;
            color: #3f3562;
            font-size: .75rem;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: .82rem 0 .42rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.95rem, 5.9vw, 3.2rem);
            line-height: 1.03;
            max-width: 14ch;
            color: #121326;
        }

        .hero h1 span { color: var(--primary); }

        .hero p {
            margin: 0;
            max-width: 52ch;
            color: rgba(18, 19, 36, .76);
            line-height: 1.47;
            font-size: .98rem;
        }

        .hero-cta {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: .62rem;
        }

        .hero-bullets {
            margin-top: .9rem;
            display: grid;
            gap: .42rem;
        }

        .hero-bullets div {
            border: 1px solid #d7d0ea;
            border-radius: .7rem;
            background: #fff;
            padding: .5rem .62rem;
            font-size: .84rem;
            color: rgba(18, 17, 35, .78);
        }

        .hero-mosaic {
            min-height: 420px;
            border-radius: var(--radius-xl);
            background: linear-gradient(165deg, #ffffff, #f6f4fb);
            border: 1px solid #d8d2ea;
            box-shadow: var(--shadow-soft);
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: .62rem;
            padding: .66rem;
        }

        .mosaic-card {
            border-radius: .95rem;
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
            background: linear-gradient(to top, rgba(16, 18, 37, .86), rgba(16, 18, 37, .08) 55%);
            z-index: 1;
        }

        .mosaic-card img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mosaic-content {
            position: relative;
            z-index: 2;
            padding: .9rem;
            width: 100%;
        }

        .mosaic-content h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: .96rem;
            line-height: 1.2;
        }

        .mosaic-content p {
            margin: .24rem 0 0;
            font-size: .74rem;
            color: rgba(255, 255, 255, .88);
            line-height: 1.35;
        }

        .phones {
            margin-top: 1.05rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .74rem;
            align-items: end;
        }

        .phone {
            border-radius: 1.6rem;
            border: 4px solid #16172a;
            overflow: hidden;
            background: #121326;
            min-height: 250px;
            position: relative;
            box-shadow: 0 16px 30px rgba(20, 19, 38, .25);
        }

        .phone::before {
            content: "";
            position: absolute;
            top: .28rem;
            left: 50%;
            transform: translateX(-50%);
            width: 38%;
            height: .66rem;
            border-radius: 999px;
            background: #11131f;
            z-index: 2;
        }

        .phone img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .phone.center {
            transform: translateY(-.35rem);
            min-height: 290px;
        }

        .band {
            margin-top: 1rem;
            background: linear-gradient(145deg, #554a7f, var(--primary));
            color: #fff;
            border-radius: 0 0 3rem 3rem;
            padding: 1.95rem 0;
            text-align: center;
            box-shadow: var(--shadow-soft);
        }

        .band h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.2rem, 3vw, 1.66rem);
        }

        .band p {
            margin: .45rem auto 0;
            max-width: 62ch;
            color: rgba(255, 255, 255, .9);
            font-size: .94rem;
        }

        .section {
            margin-top: 1.45rem;
        }

        .problem-kicker {
            display: inline-flex;
            margin: 0 auto .55rem;
            border-radius: 999px;
            border: 1px solid #cfc8e7;
            background: #f7f4ff;
            color: #514578;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            padding: .34rem .72rem;
        }

        .section h2 {
            margin: 0;
            text-align: center;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.33rem, 3.4vw, 2rem);
            color: #131427;
        }

        .section h2 span { color: var(--primary); }

        .section .lead {
            margin: .45rem auto 0;
            max-width: 62ch;
            text-align: center;
            color: var(--muted);
            font-size: .94rem;
        }

        .problem-grid {
            margin-top: .96rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .7rem;
        }

        .problem-card {
            border: 1px solid #ddd7ee;
            border-radius: .9rem;
            background: #fff;
            padding: .72rem;
            box-shadow: var(--shadow-soft);
            min-height: 170px;
            display: flex;
            flex-direction: column;
        }

        .problem-icon {
            width: 2.2rem;
            height: 2.2rem;
            border-radius: .64rem;
            display: inline-grid;
            place-items: center;
            font-family: "Space Grotesk", sans-serif;
            font-weight: 700;
            color: #4d426f;
            background: #f2eefb;
            border: 1px solid #ddd6ef;
            margin-bottom: .54rem;
        }

        .problem-card h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: .95rem;
            line-height: 1.2;
        }

        .problem-card p {
            margin: .3rem 0 0;
            color: rgba(20, 19, 37, .72);
            font-size: .83rem;
            line-height: 1.35;
        }

        .problem-card.dark {
            border-color: #1d2136;
            background: radial-gradient(circle at 95% 0%, rgba(104, 93, 148, .32), transparent 40%), linear-gradient(165deg, #121427, #0b0d1a);
            color: #fff;
        }

        .problem-card.dark h3 {
            color: #fff;
            font-size: 1.2rem;
            line-height: 1.1;
        }

        .problem-card.dark p {
            color: rgba(255, 255, 255, .82);
            margin-top: .4rem;
        }

        .problem-action {
            margin-top: auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: .72rem;
            border: 1px solid rgba(255, 255, 255, .22);
            background: rgba(255, 255, 255, .1);
            color: #fff;
            font-size: .85rem;
            font-weight: 700;
            padding: .56rem .72rem;
        }

        .highlight {
            margin-top: .95rem;
            border: 1px solid #d8d2ea;
            border-radius: var(--radius-xl);
            background: linear-gradient(145deg, #fff, #f7f5fb);
            padding: 1rem;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            gap: .8rem;
            align-items: center;
        }

        .highlight h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.12rem, 2.8vw, 1.5rem);
            color: #151628;
        }

        .highlight p {
            margin: .36rem 0 0;
            color: rgba(22, 20, 39, .72);
            line-height: 1.43;
            font-size: .9rem;
        }

        .screenshot {
            border: 1px solid #ddd6ef;
            border-radius: .9rem;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 14px 26px rgba(29, 23, 52, .15);
        }

        .screenshot img { width: 100%; display: block; }

        .testimonials {
            margin-top: 1.35rem;
            background:
                radial-gradient(circle at 14% 0%, rgba(104, 93, 148, .26), transparent 36%),
                linear-gradient(165deg, var(--ink), #0a0b18);
            color: #fff;
            border-radius: var(--radius-xl);
            padding: 1.65rem 1rem 1.1rem;
            box-shadow: var(--shadow-deep);
        }

        .testimonials .head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
        }

        .testimonials h2 {
            margin: 0;
            text-align: left;
            color: #fff;
        }

        .testimonials p.lead {
            text-align: left;
            margin: .4rem 0 0;
            color: rgba(255, 255, 255, .78);
        }

        .quote {
            width: 5.4rem;
            height: 5.4rem;
            border-radius: 50%;
            border: 1px dashed rgba(255, 255, 255, .4);
            display: grid;
            place-items: center;
            font-family: "Space Grotesk", sans-serif;
            font-size: 2rem;
            color: rgba(255, 255, 255, .9);
            flex: 0 0 auto;
        }

        .stories {
            margin-top: .95rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .7rem;
        }

        .story {
            border: 1px solid rgba(255, 255, 255, .14);
            border-radius: .9rem;
            overflow: hidden;
            background: rgba(255, 255, 255, .07);
        }

        .story img {
            display: block;
            width: 100%;
            height: 170px;
            object-fit: cover;
        }

        .story p {
            margin: 0;
            padding: .62rem;
            font-size: .81rem;
            color: rgba(255, 255, 255, .84);
            line-height: 1.35;
        }

        .features {
            margin-top: 1.4rem;
            border: 1px solid #d9d2ea;
            border-radius: var(--radius-xl);
            background: #fff;
            padding: 1.2rem .9rem;
            box-shadow: var(--shadow-soft);
        }

        .features-grid {
            margin-top: .9rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .66rem;
        }

        .feature-card {
            border: 1px solid #e2dcef;
            border-radius: .78rem;
            background: #fff;
            padding: .72rem;
        }

        .feature-card h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: .91rem;
            line-height: 1.24;
            color: #1f1a36;
        }

        .feature-card p {
            margin: .28rem 0 0;
            color: rgba(22, 20, 39, .7);
            font-size: .82rem;
            line-height: 1.35;
        }

        .why {
            margin-top: 1.3rem;
        }

        .why-grid {
            margin-top: .9rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .62rem;
        }

        .why-card {
            border: 1px solid #ddd7ee;
            border-radius: .9rem;
            background: #fff;
            padding: .72rem;
            text-align: center;
        }

        .why-card h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: .9rem;
            color: #1a1630;
        }

        .why-card p {
            margin: .28rem 0 0;
            color: rgba(20, 18, 35, .68);
            font-size: .8rem;
            line-height: 1.34;
        }

        .pricing {
            margin-top: 1.45rem;
            text-align: center;
        }

        .price-card {
            margin: .9rem auto 0;
            width: min(520px, 100%);
            border: 1px solid #d7d0ea;
            border-radius: 1rem;
            overflow: hidden;
            background: #fff;
            box-shadow: var(--shadow-soft);
        }

        .price-head {
            padding: 1rem .8rem;
            color: #fff;
            background: linear-gradient(145deg, #5d5285, var(--primary));
            text-align: center;
        }

        .chip {
            display: inline-flex;
            border: 1px solid rgba(255, 255, 255, .45);
            border-radius: 999px;
            padding: .28rem .66rem;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            background: rgba(255, 255, 255, .12);
        }

        .price-head h3 {
            margin: .52rem 0 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.24rem;
        }

        .price {
            margin-top: .26rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: 2rem;
            line-height: 1;
        }

        .price small {
            display: block;
            margin-top: .24rem;
            font-size: .78rem;
            color: rgba(255, 255, 255, .88);
            font-family: "Manrope", sans-serif;
        }

        .price-body {
            padding: .9rem;
            text-align: left;
        }

        .price-line {
            border: 1px solid #dcd5ee;
            border-radius: 999px;
            text-align: center;
            padding: .48rem .66rem;
            font-size: .82rem;
            color: #2d2850;
            background: #f8f6fc;
            font-weight: 700;
        }

        .checklist {
            margin: .72rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: .44rem;
            font-size: .84rem;
            color: rgba(20, 18, 37, .84);
        }

        .checklist li {
            display: flex;
            gap: .45rem;
            align-items: start;
            line-height: 1.35;
        }

        .checklist li::before {
            content: "";
            width: .55rem;
            height: .55rem;
            border-radius: 50%;
            margin-top: .3rem;
            background: linear-gradient(140deg, #5f5488, var(--primary));
            flex: 0 0 auto;
        }

        .faq {
            margin-top: 1.4rem;
        }

        .faq-list {
            margin-top: .86rem;
            border: 1px solid #d9d3eb;
            border-radius: .95rem;
            background: #fff;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
        }

        .faq-item + .faq-item { border-top: 1px solid #e5e0f2; }

        .faq-button {
            appearance: none;
            border: 0;
            width: 100%;
            background: transparent;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .7rem;
            padding: .8rem .86rem;
            font-family: "Space Grotesk", sans-serif;
            font-size: .94rem;
            cursor: pointer;
            color: #1b1732;
        }

        .faq-icon {
            width: 1.58rem;
            height: 1.58rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background: linear-gradient(140deg, #5c507f, var(--primary));
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            line-height: 1;
            flex: 0 0 auto;
        }

        .faq-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height .25s ease;
            padding: 0 .86rem;
            color: rgba(20, 18, 36, .72);
            font-size: .86rem;
            line-height: 1.42;
        }

        .faq-item.active .faq-content {
            max-height: 200px;
            padding: 0 .86rem .72rem;
        }

        .signup-cta {
            margin-top: 1.5rem;
            border-radius: var(--radius-xl);
            background: linear-gradient(145deg, #5c507e, var(--primary));
            color: #fff;
            text-align: center;
            padding: 1.5rem 1rem;
            box-shadow: 0 20px 44px rgba(88, 74, 132, .36);
        }

        .signup-cta h2 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: clamp(1.35rem, 3.2vw, 2rem);
        }

        .signup-cta p {
            margin: .4rem auto 0;
            max-width: 60ch;
            color: rgba(255, 255, 255, .9);
            line-height: 1.44;
            font-size: .92rem;
        }

        .signup {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: .9rem;
        }

        .panel-dark {
            background: #0f1020;
            color: #fff;
            border-radius: 1rem;
            padding: .95rem;
        }

        .panel-dark h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.26rem;
            line-height: 1.15;
        }

        .panel-dark p {
            margin: .42rem 0 0;
            color: rgba(255, 255, 255, .82);
            font-size: .88rem;
            line-height: 1.43;
        }

        .panel-links {
            margin-top: .82rem;
            display: grid;
            gap: .4rem;
        }

        .panel-links a {
            color: #d9d2ef;
            text-decoration: none;
            font-size: .83rem;
            font-weight: 700;
        }

        .form-card {
            border: 1px solid #dbd4ec;
            border-radius: 1rem;
            background: #fff;
            padding: .9rem;
            box-shadow: var(--shadow-soft);
        }

        .form-card h3 {
            margin: 0;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.27rem;
            color: #17142d;
        }

        .form-card p {
            margin: .34rem 0 0;
            color: var(--muted);
            font-size: .88rem;
        }

        .errors {
            margin-top: .62rem;
            border: 1px solid #fecaca;
            border-radius: .66rem;
            background: #fef2f2;
            color: #991b1b;
            padding: .54rem .64rem;
            font-size: .82rem;
            font-weight: 700;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .62rem;
        }

        .field { margin-top: .62rem; }

        label {
            display: block;
            margin-bottom: .28rem;
            font-size: .81rem;
            font-weight: 700;
            color: #231e3f;
        }

        input {
            width: 100%;
            border: 1px solid #e1dbf0;
            border-radius: .68rem;
            padding: .68rem .74rem;
            font: inherit;
            color: #19152f;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(104, 93, 148, .2);
        }

        .submit {
            width: 100%;
            margin-top: .8rem;
            border: 0;
            border-radius: .72rem;
            padding: .8rem 1rem;
            font: 700 .91rem "Manrope", sans-serif;
            color: #fff;
            background: linear-gradient(145deg, #5d5285, var(--primary));
            cursor: pointer;
        }

        .footer {
            margin: 1.3rem 0 1.75rem;
            text-align: center;
            color: rgba(20, 19, 36, .62);
            font-size: .83rem;
            font-weight: 700;
        }

        @media (max-width: 1080px) {
            .menu { display: none; }
            .hero { grid-template-columns: 1fr; }
            .problem-grid,
            .features-grid,
            .why-grid,
            .stories { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .highlight,
            .signup { grid-template-columns: 1fr; }
            .testimonials .head {
                display: grid;
                gap: .7rem;
            }
        }

        @media (max-width: 760px) {
            .container { width: min(1160px, calc(100% - 1rem)); }
            .powered-inline,
            .menu { display: none; }
            .header { border-radius: .95rem; }
            .hero,
            .section,
            .features,
            .pricing,
            .faq,
            .signup { margin-top: 1rem; }
            .hero-mosaic { grid-template-columns: 1fr; min-height: auto; }
            .mosaic-card { min-height: 160px; }
            .phones { grid-template-columns: 1fr; }
            .phone.center { transform: none; }
            .problem-grid,
            .features-grid,
            .why-grid,
            .stories,
            .grid-2 { grid-template-columns: 1fr; }
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
                    <a href="#problemas">Problemas</a>
                    <a href="#funcionalidades">Funcionalidades</a>
                    <a href="#preco">Preco</a>
                    <a href="#faq">FAQ</a>
                </nav>

                <div class="header-actions">
                    <div class="powered-inline" aria-label="Powered by Cheesemania">
                        <span>Powered by</span>
                        <img src="{{ asset('images/cheesemania.png') }}" alt="Cheesemania">
                    </div>
                    <a class="btn btn-light" href="{{ url('/admin/login') }}">Entrar</a>
                    <a class="btn btn-main" href="#cadastro">Criar conta</a>
                </div>
            </header>
        </div>
    </div>

    <main class="container">
        <section class="hero">
            <div>
                <span class="kicker">Para confeitaria, padaria e salgados</span>
                <h1>Controle completo para vender com <span>lucro real e previsivel</span></h1>
                <p>Unifique receitas, lotes, estoque, vendas offline e financeiro, com entrada por unidade amigavel: kg, g, L, ml, colheres, chavena ou unidade.</p>

                <div class="hero-cta">
                    <a class="btn btn-main" href="#cadastro">Comecar agora</a>
                    <a class="btn btn-dark" href="#preco">Ver plano atual</a>
                </div>

                <div class="hero-bullets">
                    <div>Informe na unidade que quiser; o sistema converte automaticamente.</div>
                    <div>Preco unitario automatico quando a venda e feita por receita.</div>
                    <div>Referencia automatica e alerta visual para produzir e vender com seguranca.</div>
                </div>
            </div>

            <aside class="hero-mosaic">
                <article class="mosaic-card">
                    <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Painel financeiro StockPulse" loading="lazy">
                    <div class="mosaic-content">
                        <h3>Financeiro claro</h3>
                        <p>Receitas, despesas e ganhos por periodo.</p>
                    </div>
                </article>
                <article class="mosaic-card">
                    <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Registo de vendas" loading="lazy">
                    <div class="mosaic-content">
                        <h3>Vendas</h3>
                        <p>Offline e online.</p>
                    </div>
                </article>
                <article class="mosaic-card">
                    <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Producao e stock" loading="lazy">
                    <div class="mosaic-content">
                        <h3>Producao</h3>
                        <p>Custo por lote.</p>
                    </div>
                </article>
            </aside>
        </section>

        <section class="phones">
            <figure class="phone">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela do StockPulse" loading="lazy">
            </figure>
            <figure class="phone center">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela principal do StockPulse" loading="lazy">
            </figure>
            <figure class="phone">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Tela de vendas do StockPulse" loading="lazy">
            </figure>
        </section>
    </main>

    <section class="band">
        <div class="container">
            <h2>StockPulse foi construido para a rotina real da producao alimentar.</h2>
            <p>Decida com dados: o que vender, quanto produzir, quando comprar, quanto perdeu e onde esta sua margem liquida.</p>
        </div>
    </section>

    <section id="problemas" class="container section">
        <div class="problem-kicker">Identifique os sinais</div>
        <h2>Sua operacao esta <span>deixando dinheiro na mesa?</span></h2>
        <p class="lead">Se voce se identifica com estes pontos, eles podem estar travando seu lucro e sua capacidade de crescer com seguranca.</p>

        <div class="problem-grid">
            <article class="problem-card">
                <span class="problem-icon">01</span>
                <h3>Precificacao sem custo real</h3>
                <p>Vende bem, mas nao sabe exatamente quanto lucra por produto.</p>
            </article>
            <article class="problem-card">
                <span class="problem-icon">02</span>
                <h3>Conversao manual estressante</h3>
                <p>Perde tempo convertendo kg, g, L, ml e medidas de cozinha no papel.</p>
            </article>
            <article class="problem-card">
                <span class="problem-icon">03</span>
                <h3>Falta de insumo no pior momento</h3>
                <p>Perde venda por nao enxergar disponibilidade real para produzir.</p>
            </article>
            <article class="problem-card">
                <span class="problem-icon">04</span>
                <h3>Venda offline sem rastreio</h3>
                <p>Dinheiro circula, mas o financeiro nao mostra tudo que foi movimentado.</p>
            </article>
            <article class="problem-card">
                <span class="problem-icon">05</span>
                <h3>Quebras sem motivo registrado</h3>
                <p>Perdas e desperdicios acontecem, mas sem contexto para melhorar.</p>
            </article>
            <article class="problem-card dark">
                <h3>Parece familiar?</h3>
                <p>StockPulse conecta producao, vendas e financeiro para voce decidir com dados reais todos os dias.</p>
                <a class="problem-action" href="#funcionalidades">Conhecer a solucao</a>
            </article>
        </div>

        <article class="highlight">
            <div>
                <h3>Transforme desorganizacao em lucro previsivel</h3>
                <p>Com unidades inteligentes, conversao automatica por densidade e automacao de venda para financeiro, voce enxerga resultado liquido sem planilhas.</p>
                <div class="hero-cta" style="justify-content:flex-start; margin-top:.75rem;">
                    <a class="btn btn-main" href="#cadastro">Criar conta gratis</a>
                </div>
            </div>
            <figure class="screenshot">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Painel de controle financeiro" loading="lazy">
            </figure>
        </article>
    </section>

    <section class="container testimonials">
        <div class="head">
            <div>
                <h2>Negocios reais, resultados reais</h2>
                <p class="lead">Padarias e confeitarias usam o StockPulse para reduzir perdas e melhorar margem.</p>
            </div>
            <div class="quote">"</div>
        </div>

        <div class="stories">
            <article class="story">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Relato de cliente 1" loading="lazy">
                <p>"Agora eu vejo ganho por periodo e consigo reajustar preco com confianca."</p>
            </article>
            <article class="story">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Relato de cliente 2" loading="lazy">
                <p>"Passei a registrar vendas offline e o caixa ficou previsivel."</p>
            </article>
            <article class="story">
                <img src="{{ asset('images/stockpulse-financial-control-demo.png') }}" alt="Relato de cliente 3" loading="lazy">
                <p>"Com alerta de estoque, evito prometer o que nao consigo entregar."</p>
            </article>
        </div>
    </section>

    <section id="funcionalidades" class="container features section">
        <h2>Tudo que sua confeitaria precisa em um so lugar</h2>
        <p class="lead">Do custo da receita ate o fechamento financeiro da venda, agora com conversao de unidades sem friccao.</p>

        <div class="features-grid">
            <article class="feature-card"><h3>Unidades inteligentes</h3><p>Informe kg, g, L, ml, colher, chavena ou unidade; o sistema converte automaticamente.</p></article>
            <article class="feature-card"><h3>Densidade por ingrediente</h3><p>Converta volume em peso com precisao (agua 1, oleo 0,92, mel 1,42 ou valor personalizado).</p></article>
            <article class="feature-card"><h3>Receitas com custo real</h3><p>Custo por lote e por unidade considerando insumos, embalagem e custos indiretos.</p></article>
            <article class="feature-card"><h3>Lotes de producao com validacao</h3><p>Veja imediatamente se da para produzir e o que esta a faltar.</p></article>
            <article class="feature-card"><h3>Vendas offline e online</h3><p>Registre tudo, com preco automatico para item de receita e referencia gerada automaticamente.</p></article>
            <article class="feature-card"><h3>Financeiro por periodo</h3><p>Receitas, despesas, perdas, compras, pendentes e movimento liquido em um painel unico.</p></article>
        </div>
    </section>

    <section class="container why section">
        <h2>Por que escolher o StockPulse?</h2>
        <p class="lead">Feito para quem produz e vende todos os dias.</p>

        <div class="why-grid">
            <article class="why-card"><h3>Pratico</h3><p>Interface simples para usar no dia a dia.</p></article>
            <article class="why-card"><h3>Rastreavel</h3><p>Historico completo de lotes, vendas e transacoes.</p></article>
            <article class="why-card"><h3>Confiavel</h3><p>Automacao reduz erro manual na operacao.</p></article>
            <article class="why-card"><h3>Escalavel</h3><p>Estrutura pronta para crescer com seu negocio.</p></article>
        </div>
    </section>

    <section id="preco" class="container pricing section">
        <h2>Comece hoje</h2>
        <p class="lead">Plano unico com acesso total durante a fase atual do StockPulse.</p>

        <article class="price-card">
            <header class="price-head">
                <span class="chip">Atualmente gratis</span>
                <h3>Plano completo</h3>
                <div class="price">0,00 MT<small>No futuro o preco pode mudar.</small></div>
            </header>
            <div class="price-body">
                <div class="price-line">Acesso a todas as funcionalidades principais</div>
                <ul class="checklist">
                    <li>Receitas, ingredientes e controlo de estoque com unidade amigavel</li>
                    <li>Conversao automatica de medidas e densidade por ingrediente</li>
                    <li>Vendas com preco e referencia automatica</li>
                    <li>Painel financeiro completo por periodo</li>
                    <li>Registo de perdas e compras com motivo/descricao</li>
                </ul>
                <div class="hero-cta" style="justify-content:flex-start; margin-top:.75rem;">
                    <a class="btn btn-main" href="#cadastro">Criar conta agora</a>
                </div>
            </div>
        </article>
    </section>

    <section id="faq" class="container faq section">
        <h2>Perguntas frequentes</h2>
        <p class="lead">Respostas diretas para comecar sem bloqueio.</p>

        <div class="faq-list">
            <article class="faq-item active">
                <button class="faq-button" type="button">
                    <span>Posso registrar venda sem receita?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">Sim. Venda avulsa offline ou online pode ser registrada e refletida no financeiro.</div>
            </article>
            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>Preciso calcular tudo em gramas manualmente?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">Nao. Voce pode usar kg, g, L, ml, colher, chavena ou unidade, e o sistema converte automaticamente.</div>
            </article>
            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>O preco unitario da receita e automatico?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">Sim. Ao vender item de receita, o valor unitario e preenchido automaticamente.</div>
            </article>
            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>Consigo controlar perdas e compras?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">Sim. Perdas, compras e outras transacoes entram no painel com motivo e referencia.</div>
            </article>
            <article class="faq-item">
                <button class="faq-button" type="button">
                    <span>O plano esta mesmo em 0,00 MT agora?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-content">Sim. Atualmente o plano esta gratis em 0,00 MT. Esse valor pode ser ajustado no futuro.</div>
            </article>
        </div>
    </section>

    <section class="container signup-cta">
        <h2>Transforme sua operacao hoje</h2>
        <p>Crie sua conta em minutos e passe a decidir com numeros reais de producao, venda e financeiro.</p>
    </section>

    <section id="cadastro" class="container signup section">
        <aside class="panel-dark">
            <h3>Comece gratis no StockPulse</h3>
            <p>Depois do cadastro, voce entra direto no painel para operar com controle total.</p>
            <div style="margin-top:.8rem;">
                <img src="{{ asset('images/cheesemania.png') }}" alt="Cheesemania" style="height:1.35rem; width:auto;">
            </div>
            <div class="panel-links">
                <a href="{{ url('/admin/login') }}">Ja tem conta? Entrar</a>
                <a href="{{ route('landing') }}">Voltar ao topo</a>
            </div>
        </aside>

        <article class="form-card">
            <h3>Criar conta</h3>
            <p>Preencha os dados e aceda imediatamente.</p>

            @if ($errors->any())
                <div class="errors">{{ $errors->first() }}</div>
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
