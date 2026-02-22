<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockPulse | Gestão para Padaria e Salgados</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fff8ea;
            --surface: #fffdf7;
            --surface-soft: #fff6df;
            --text: #261a09;
            --muted: #6b5a40;
            --line: #ecd8ab;
            --gold: #c78b1e;
            --gold-strong: #9b680f;
            --gold-soft: #fff1cf;
            --ok: #0f766e;
            --danger: #b91c1c;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Manrope", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 88% 0%, rgba(199, 139, 30, 0.24), transparent 36%),
                radial-gradient(circle at 0% 16%, rgba(255, 222, 154, 0.52), transparent 34%),
                var(--bg);
            padding: 1rem;
        }

        .page {
            width: min(1180px, 100%);
            margin: 0 auto;
            display: grid;
            gap: 0.95rem;
        }

        .topbar {
            border: 1px solid var(--line);
            background: rgba(255, 253, 247, 0.84);
            border-radius: 1rem;
            padding: 0.8rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-family: "Sora", sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .brand-badge {
            width: 1.85rem;
            height: 1.85rem;
            border-radius: 0.55rem;
            display: inline-grid;
            place-items: center;
            background: linear-gradient(150deg, #f7c866, var(--gold-strong));
            color: #fffdf7;
            font-size: 0.86rem;
            font-weight: 800;
            box-shadow: 0 10px 18px rgba(155, 104, 15, 0.24);
        }

        .powered {
            font-size: 0.83rem;
            color: var(--muted);
            font-weight: 700;
        }

        .hero {
            border-radius: 1.25rem;
            border: 1px solid #8a5a0e;
            background:
                radial-gradient(circle at 72% 0%, rgba(251, 211, 122, 0.28), transparent 40%),
                linear-gradient(150deg, #6b4408, #8d5c0c 52%, #6e470a);
            color: #fff7e8;
            padding: 1.25rem;
            display: grid;
            gap: 0.9rem;
            grid-template-columns: 1.25fr 0.9fr;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 231, 176, 0.6);
            background: rgba(61, 39, 4, 0.42);
            color: #ffe7b2;
            padding: 0.35rem 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.75rem;
        }

        h1 {
            margin: 0.7rem 0 0;
            font-family: "Sora", sans-serif;
            line-height: 1.08;
            font-size: clamp(1.85rem, 4.8vw, 3rem);
            max-width: 15ch;
        }

        .hero p {
            margin: 0.8rem 0 0;
            color: #ffedc8;
            line-height: 1.45;
            max-width: 56ch;
        }

        .hero-cta {
            margin-top: 1rem;
            display: flex;
            gap: 0.65rem;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            border-radius: 0.72rem;
            padding: 0.72rem 1rem;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 800;
            transition: transform .2s ease;
        }

        .btn:hover { transform: translateY(-1px); }

        .btn-main {
            background: linear-gradient(180deg, #ffd684, #f7b83f);
            color: #503301;
            box-shadow: 0 10px 22px rgba(251, 190, 72, 0.28);
        }

        .btn-ghost {
            border: 1px solid rgba(255, 227, 166, 0.6);
            color: #fff4db;
            background: rgba(70, 44, 5, 0.4);
        }

        .hero-list {
            border: 1px solid rgba(255, 225, 159, 0.42);
            background: rgba(53, 34, 4, 0.44);
            border-radius: 0.9rem;
            padding: 0.85rem;
        }

        .hero-list h3 {
            margin: 0;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #ffe0a3;
            font-weight: 800;
        }

        .hero-list ul {
            margin: 0.65rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.42rem;
        }

        .hero-list li {
            border: 1px solid rgba(255, 221, 145, 0.24);
            background: rgba(89, 56, 7, 0.42);
            border-radius: 0.62rem;
            padding: 0.55rem 0.65rem;
            color: #fff4db;
            font-size: 0.88rem;
        }

        .hero-list li strong {
            display: block;
            margin-bottom: 0.12rem;
            font-size: 0.79rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #ffd27c;
        }

        .demo {
            border: 1px solid var(--line);
            background: var(--surface);
            border-radius: 1rem;
            padding: 1rem;
        }

        .demo h2 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 1.3rem;
        }

        .demo p {
            margin: 0.45rem 0 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .demo-frame {
            margin: 0.9rem 0 0;
            border: 1px solid #dfc286;
            border-radius: 0.85rem;
            overflow: hidden;
            background: #f6ead0;
            min-height: 260px;
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
            padding: 1rem;
            text-align: center;
            color: #6b4b12;
            font-weight: 700;
            font-size: 0.92rem;
            background: linear-gradient(180deg, #fff6de, #f6e4b9);
        }

        .demo-note {
            margin-top: 0.55rem;
            font-size: 0.81rem;
            color: #6b5a40;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.8rem;
        }

        .feature {
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            background: var(--surface);
            padding: 0.9rem;
        }

        .feature h3 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 1rem;
        }

        .feature p {
            margin: 0.45rem 0 0;
            color: var(--muted);
            font-size: 0.89rem;
            line-height: 1.42;
        }

        .feature .label {
            margin-top: 0.6rem;
            display: inline-flex;
            border-radius: 9999px;
            padding: 0.2rem 0.5rem;
            font-size: 0.74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #8f5e0f;
            background: var(--gold-soft);
            border: 1px solid #f0d294;
        }

        .signup {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.85rem;
        }

        .panel, .form {
            border: 1px solid var(--line);
            border-radius: 0.95rem;
            background: var(--surface);
            padding: 1rem;
        }

        .panel h2, .form h2 {
            margin: 0;
            font-family: "Sora", sans-serif;
            font-size: 1.28rem;
        }

        .panel p, .form p {
            margin: 0.45rem 0 0;
            color: var(--muted);
            font-size: 0.91rem;
        }

        .benefits {
            margin-top: 0.8rem;
            display: grid;
            gap: 0.52rem;
        }

        .benefit {
            border: 1px solid #efd9ab;
            border-radius: 0.7rem;
            background: var(--surface-soft);
            padding: 0.58rem 0.68rem;
            color: #4e3a17;
            font-size: 0.9rem;
            line-height: 1.35;
        }

        .benefit strong {
            display: block;
            color: #87570c;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.12rem;
        }

        .errors {
            margin: 0 0 0.75rem;
            border-radius: 0.65rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: var(--danger);
            font-size: 0.84rem;
            font-weight: 700;
            padding: 0.56rem 0.65rem;
        }

        .field {
            margin-bottom: 0.76rem;
        }

        label {
            display: block;
            margin-bottom: 0.32rem;
            font-size: 0.86rem;
            font-weight: 700;
        }

        input {
            width: 100%;
            border: 1px solid #e6cb90;
            border-radius: 0.7rem;
            padding: 0.72rem 0.8rem;
            font: inherit;
            color: var(--text);
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            outline: none;
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(199, 139, 30, 0.18);
        }

        .submit {
            width: 100%;
            border: 0;
            border-radius: 0.72rem;
            padding: 0.82rem 1rem;
            font: 800 0.95rem "Manrope", sans-serif;
            color: #fffaf2;
            background: linear-gradient(180deg, var(--gold), var(--gold-strong));
            cursor: pointer;
        }

        .form-links {
            margin-top: 0.78rem;
            display: flex;
            justify-content: space-between;
            gap: 0.6rem;
            flex-wrap: wrap;
            font-size: 0.86rem;
        }

        .form-links a {
            color: #8f5c0d;
            text-decoration: none;
            font-weight: 800;
        }

        .status {
            margin-top: 0.62rem;
            border: 1px solid #a7f3d0;
            border-radius: 0.65rem;
            background: #ecfdf5;
            color: var(--ok);
            font-size: 0.82rem;
            font-weight: 700;
            padding: 0.55rem 0.68rem;
        }

        @media (max-width: 1040px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .features {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .signup {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            body {
                padding: 0.75rem;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .hero, .feature, .panel, .form, .demo, .topbar {
                border-radius: 0.82rem;
            }
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
            <div class="powered">Powered by Cheesemania</div>
        </header>

        <section class="hero">
            <div>
                <span class="tag">Gestão completa para padaria e salgados</span>
                <h1>Controle produção, vendas e dinheiro do seu negócio em um só painel.</h1>
                <p>
                    O StockPulse foi feito para quem produz e vende alimentos todos os dias.
                    Você acompanha o custo para produzir, o valor vendido, o lucro real, as perdas e o nível de estoque
                    sem depender de planilhas.
                </p>
                <div class="hero-cta">
                    <a class="btn btn-main" href="#cadastro">Criar conta grátis</a>
                    <a class="btn btn-ghost" href="{{ url('/admin/login') }}">Entrar no painel</a>
                </div>
            </div>

            <aside class="hero-list">
                <h3>O que você recebe</h3>
                <ul>
                    <li>
                        <strong>Custos de produção claros</strong>
                        Veja o custo total de cada lote e quanto sobra de lucro por venda.
                    </li>
                    <li>
                        <strong>Vendas registradas com precisão</strong>
                        Registre vendas presenciais e digitais para medir o movimento real do caixa.
                    </li>
                    <li>
                        <strong>Referência automática em cada venda</strong>
                        O sistema cria código de referência para facilitar rastreio e conferência.
                    </li>
                    <li>
                        <strong>Alertas de estoque antes da produção</strong>
                        Saiba com antecedência quando faltar ingrediente para produzir.
                    </li>
                </ul>
            </aside>
        </section>

        <section class="demo">
            <h2>Demonstração do painel financeiro</h2>
            <p>
                Exemplo real da tela de controlo financeiro, com filtros por período, métricas de receitas e despesas
                e visão clara do desempenho do negócio.
            </p>
            <figure class="demo-frame">
                <img
                    src="{{ asset('images/stockpulse-financial-control-demo.png') }}"
                    alt="Demonstração do painel de controlo financeiro do StockPulse"
                    loading="lazy"
                    onerror="this.closest('.demo-frame').classList.add('missing'); this.remove();"
                >
            </figure>
        </section>

        <section class="features">
            <article class="feature">
                <h3>Controle de receitas e ingredientes</h3>
                <p>Cadastre receitas, consumo por ingrediente e acompanhe o valor de cada item usado na produção.</p>
                <span class="label">Produção</span>
            </article>

            <article class="feature">
                <h3>Preço unitário calculado automaticamente</h3>
                <p>Quando a venda é feita com base em receita, o sistema preenche o preço unitário automaticamente.</p>
                <span class="label">Automação</span>
            </article>

            <article class="feature">
                <h3>Código de venda gerado na hora</h3>
                <p>Cada venda recebe referência automática para facilitar busca, auditoria e organização.</p>
                <span class="label">Rastreio</span>
            </article>

            <article class="feature">
                <h3>Indicador visual de viabilidade</h3>
                <p>Antes de produzir ou vender, o sistema mostra em cores quando é possível continuar ou quando falta estoque.</p>
                <span class="label">Alertas</span>
            </article>

            <article class="feature">
                <h3>Painel financeiro de fácil leitura</h3>
                <p>Visualize total vendido, total gasto, lucro, perdas e ticket médio no mesmo local.</p>
                <span class="label">Financeiro</span>
            </article>

            <article class="feature">
                <h3>Valores e quantidades padronizados</h3>
                <p>Dinheiro sempre no formato local e quantidades sem casas decimais para evitar confusão na operação.</p>
                <span class="label">Precisão</span>
            </article>
        </section>

        <section id="cadastro" class="signup">
            <article class="panel">
                <h2>Tenha clareza total do seu negócio</h2>
                <p>Cadastre-se e comece a controlar tudo com linguagem simples e dados confiáveis.</p>

                <div class="benefits">
                    <div class="benefit">
                        <strong>Custos reais por lote</strong>
                        Entenda quanto você gasta para produzir cada receita e quanto realmente ganha por venda.
                    </div>
                    <div class="benefit">
                        <strong>Fluxo financeiro completo</strong>
                        Acompanhe entradas e saídas para ver com clareza o resultado final do período.
                    </div>
                    <div class="benefit">
                        <strong>Decisão com base em dados</strong>
                        Ajuste preços, produção e compras com base no que está acontecendo na operação.
                    </div>
                </div>
            </article>

            <article class="form">
                <h2>Criar conta</h2>
                <p>Sem cartão e com acesso imediato ao painel.</p>

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

                <div class="form-links">
                    <span>Já tem acesso? <a href="{{ url('/admin/login') }}">Entrar</a></span>
                    <a href="{{ url('/') }}">Home</a>
                </div>

                <div class="status">Ao cadastrar, você entra direto no dashboard.</div>
            </article>
        </section>
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
