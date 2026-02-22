<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockPulse | Gestão para Produção e Vendas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #685D94;
            --soft-1: #E0DDE9;
            --soft-2: #CEC7E5;
            --soft-3: #F0EFF4;
            --white: #FFFFFF;
            --black: #000000;
            --muted: rgba(0, 0, 0, 0.67);
            --ok: #0f766e;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Manrope", sans-serif;
            color: var(--black);
            background:
                radial-gradient(circle at 92% -5%, rgba(104, 93, 148, 0.20), transparent 35%),
                radial-gradient(circle at 0% 18%, rgba(224, 221, 233, 0.75), transparent 32%),
                var(--soft-3);
            padding: 1rem;
        }

        .page {
            width: min(1160px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 0.9rem;
        }

        .topbar {
            border: 1px solid var(--soft-2);
            background: rgba(255, 255, 255, 0.85);
            border-radius: 1rem;
            padding: 0.8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.6rem;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-family: "Sora", sans-serif;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .brand-badge {
            width: 1.8rem;
            height: 1.8rem;
            border-radius: 0.5rem;
            display: inline-grid;
            place-items: center;
            color: var(--white);
            background: var(--primary);
            font-size: 0.84rem;
            font-weight: 800;
            box-shadow: 0 10px 18px rgba(104, 93, 148, 0.32);
        }

        .powered-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--soft-2);
            background: var(--white);
            border-radius: 0.6rem;
            padding: 0.24rem 0.42rem;
        }

        .powered-logo img {
            display: block;
            height: 1.28rem;
            width: auto;
        }

        .hero {
            border: 1px solid rgba(255, 255, 255, 0.24);
            border-radius: 1.2rem;
            background:
                radial-gradient(circle at 76% 0%, rgba(255, 255, 255, 0.18), transparent 40%),
                linear-gradient(152deg, #4f4771, var(--primary));
            color: var(--white);
            padding: 1.2rem;
            display: grid;
            grid-template-columns: 1.25fr 0.9fr;
            gap: 0.9rem;
        }

        .tag {
            display: inline-flex;
            border: 1px solid rgba(255, 255, 255, 0.42);
            border-radius: 9999px;
            padding: 0.34rem 0.7rem;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            background: rgba(255, 255, 255, 0.12);
        }

        h1 {
            margin: 0.75rem 0 0;
            font-family: "Sora", sans-serif;
            line-height: 1.08;
            font-size: clamp(1.85rem, 4.6vw, 2.9rem);
            max-width: 14ch;
        }

        .hero p {
            margin: 0.78rem 0 0;
            max-width: 54ch;
            color: rgba(255, 255, 255, 0.92);
            line-height: 1.45;
        }

        .hero-cta {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.62rem;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            border-radius: 0.72rem;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
            font-weight: 800;
            transition: transform .2s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-main {
            color: var(--primary);
            background: var(--white);
        }

        .btn-ghost {
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.12);
        }

        .hero-points {
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 0.85rem;
            background: rgba(255, 255, 255, 0.11);
            padding: 0.8rem;
        }

        .hero-points h3 {
            margin: 0;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .hero-points ul {
            margin: 0.58rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.4rem;
        }

        .hero-points li {
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 0.6rem;
            padding: 0.48rem 0.58rem;
            font-size: 0.86rem;
            background: rgba(255, 255, 255, 0.1);
        }

        .demo {
            border: 1px solid var(--soft-2);
            background: var(--white);
            border-radius: 0.95rem;
            padding: 1rem;
        }

        .demo h2 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 1.25rem;
        }

        .demo p {
            margin: 0.4rem 0 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .demo-frame {
            margin-top: 0.8rem;
            border: 1px solid var(--soft-2);
            border-radius: 0.8rem;
            overflow: hidden;
            background: var(--soft-3);
            min-height: 250px;
            position: relative;
        }

        .demo-frame img {
            display: block;
            width: 100%;
            height: auto;
        }

        .demo-frame.missing::after {
            content: "Adicione a imagem em public/images/stockpulse-financial-control-demo.png";
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            text-align: center;
            padding: 1rem;
            color: var(--primary);
            font-weight: 700;
            font-size: 0.9rem;
            background: var(--soft-3);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .feature {
            border: 1px solid var(--soft-2);
            border-radius: 0.85rem;
            background: var(--white);
            padding: 0.84rem;
        }

        .feature h3 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 0.96rem;
            line-height: 1.25;
        }

        .feature p {
            margin: 0.4rem 0 0;
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.35;
        }

        .signup {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.82rem;
        }

        .panel, .form {
            border: 1px solid var(--soft-2);
            border-radius: 0.92rem;
            background: var(--white);
            padding: 0.95rem;
        }

        .panel h2, .form h2 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 1.23rem;
        }

        .panel p, .form p {
            margin: 0.42rem 0 0;
            color: var(--muted);
            font-size: 0.89rem;
        }

        .benefits {
            margin-top: 0.75rem;
            display: grid;
            gap: 0.46rem;
        }

        .benefit {
            border: 1px solid var(--soft-2);
            background: var(--soft-3);
            border-radius: 0.66rem;
            padding: 0.5rem 0.62rem;
            font-size: 0.86rem;
            line-height: 1.35;
            color: #201b2d;
        }

        .errors {
            margin: 0 0 0.72rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger);
            border-radius: 0.62rem;
            padding: 0.56rem 0.65rem;
            font-size: 0.84rem;
            font-weight: 700;
        }

        .field { margin-bottom: 0.74rem; }

        label {
            display: block;
            margin-bottom: 0.3rem;
            font-size: 0.85rem;
            font-weight: 700;
        }

        input {
            width: 100%;
            border: 1px solid var(--soft-2);
            border-radius: 0.66rem;
            padding: 0.7rem 0.78rem;
            font: inherit;
            color: var(--black);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(104, 93, 148, 0.2);
        }

        .submit {
            width: 100%;
            border: 0;
            border-radius: 0.7rem;
            padding: 0.8rem 1rem;
            font: 800 0.94rem "Manrope", sans-serif;
            color: var(--white);
            background: linear-gradient(180deg, #786ca9, var(--primary));
            cursor: pointer;
        }

        .links {
            margin-top: 0.72rem;
            display: flex;
            justify-content: space-between;
            gap: 0.6rem;
            flex-wrap: wrap;
            font-size: 0.85rem;
        }

        .links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 800;
        }

        .status {
            margin-top: 0.6rem;
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: var(--ok);
            border-radius: 0.62rem;
            padding: 0.52rem 0.65rem;
            font-size: 0.81rem;
            font-weight: 700;
        }

        .footer-powered {
            margin: 0.15rem 0 0;
            text-align: center;
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            .hero { grid-template-columns: 1fr; }
            .features { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .signup { grid-template-columns: 1fr; }
        }

        @media (max-width: 680px) {
            body { padding: 0.72rem; }
            .features { grid-template-columns: 1fr; }
            .topbar, .hero, .demo, .feature, .panel, .form { border-radius: 0.78rem; }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="brand">
                <span class="brand-badge">SP</span>
                <span>StockPulse</span>
            </div>
            <div class="powered-logo">
                <img src="{{ asset('images/cheesemania.png') }}" alt="Cheesemania">
            </div>
        </header>

        <section class="hero">
            <div>
                <span class="tag">Feito para produção alimentar</span>
                <h1>Produza melhor. Venda melhor. Lucre com clareza.</h1>
                <p>Controle custos, vendas, perdas e estoque em um só lugar.</p>
                <div class="hero-cta">
                    <a class="btn btn-main" href="#cadastro">Criar conta grátis</a>
                    <a class="btn btn-ghost" href="{{ url('/admin/login') }}">Entrar no painel</a>
                </div>
            </div>

            <aside class="hero-points">
                <h3>Impacto direto</h3>
                <ul>
                    <li>Preço de venda calculado automaticamente para itens de receita.</li>
                    <li>Código de referência gerado em cada venda.</li>
                    <li>Alerta visual quando faltar estoque para produzir ou vender.</li>
                </ul>
            </aside>
        </section>

        <section class="demo">
            <h2>Veja o painel financeiro em ação</h2>
            <p>Filtros por período e visão clara de receitas, despesas e resultado.</p>
            <figure class="demo-frame">
                <img
                    src="{{ asset('images/stockpulse-financial-control-demo.png') }}"
                    alt="Painel financeiro do StockPulse"
                    loading="lazy"
                    onerror="this.closest('.demo-frame').classList.add('missing'); this.remove();"
                >
            </figure>
        </section>

        <section class="features">
            <article class="feature">
                <h3>Receitas e ingredientes</h3>
                <p>Saiba exatamente quanto custa produzir cada item.</p>
            </article>

            <article class="feature">
                <h3>Vendas presenciais e digitais</h3>
                <p>Registre tudo e acompanhe o dinheiro em movimento.</p>
            </article>

            <article class="feature">
                <h3>Relatórios financeiros diretos</h3>
                <p>Veja total vendido, total gasto, lucro e perdas.</p>
            </article>

            <article class="feature">
                <h3>Dados padronizados</h3>
                <p>Valores monetários e quantidades sempre consistentes.</p>
            </article>
        </section>

        <section id="cadastro" class="signup">
            <article class="panel">
                <h2>Clareza para decidir rápido</h2>
                <p>Use números reais para comprar, produzir e vender com segurança.</p>

                <div class="benefits">
                    <div class="benefit">Veja o custo total de produção de cada lote.</div>
                    <div class="benefit">Entenda o lucro por venda em tempo real.</div>
                    <div class="benefit">Evite produção sem estoque suficiente.</div>
                </div>
            </article>

            <article class="form">
                <h2>Criar conta</h2>
                <p>Acesso imediato ao painel.</p>

                @if ($errors->any())
                    <div class="errors">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('landing.signup') }}">
                    @csrf

                    <div class="field">
                        <label for="name">Nome</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label for="email">Email de contacto</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>

                    <div class="field">
                        <label for="contact_number">Número de contacto</label>
                        <input id="contact_number" name="contact_number" type="tel" value="{{ old('contact_number') }}" placeholder="+258 84 123 4567" required>
                    </div>

                    <div class="field">
                        <label for="password">Senha</label>
                        <input id="password" name="password" type="password" minlength="8" required>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar senha</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
                    </div>

                    <button class="submit" type="submit">Criar conta e entrar no painel</button>
                </form>

                <div class="links">
                    <span>Já tem acesso? <a href="{{ url('/admin/login') }}">Entrar</a></span>
                    <a href="{{ url('/') }}">Home</a>
                </div>

                <div class="status">Ao cadastrar, você entra direto no dashboard.</div>
            </article>
        </section>

        <footer class="footer-powered">Powered by Cheesemania</footer>
    </main>

    <script>
    (() => {
        const input = document.getElementById('contact_number');
        if (!input) return;

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
    })();
    </script>
</body>
</html>
