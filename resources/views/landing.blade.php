<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockPulse | Precificação</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fff8ee;
            --surface: #fffefb;
            --text: #311f1a;
            --muted: #735f57;
            --accent: #ff8a3d;
            --accent-dark: #e56b1f;
            --ring: #ffd8bc;
            --ok: #0f766e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Outfit", sans-serif;
            color: var(--text);
            min-height: 100vh;
            background:
                radial-gradient(circle at 10% 0%, #ffe7d2 0%, transparent 35%),
                radial-gradient(circle at 100% 20%, #ffd9c2 0%, transparent 30%),
                var(--bg);
            display: grid;
            place-items: center;
            padding: 2rem 1rem;
        }

        .layout {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: 1.2fr 0.9fr;
            gap: 1.25rem;
        }

        .hero, .card {
            background: var(--surface);
            border: 1px solid #f4dfcf;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(86, 46, 26, 0.08);
        }

        .hero {
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.75rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .45rem .85rem;
            border-radius: 999px;
            background: #fff1e4;
            color: #8b4519;
            font-weight: 600;
            font-size: .875rem;
            width: fit-content;
        }

        .brand {
            margin: 0 0 .75rem;
            font-family: "Sora", sans-serif;
            font-size: clamp(1.3rem, 2.3vw, 1.7rem);
            font-weight: 800;
            letter-spacing: .01em;
            color: #7c2d12;
        }

        h1 {
            margin: .8rem 0 0;
            font-family: "Sora", sans-serif;
            font-size: clamp(2rem, 4.2vw, 3rem);
            line-height: 1.06;
        }

        p {
            margin: .9rem 0 0;
            color: var(--muted);
            font-size: 1.05rem;
            max-width: 56ch;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: .75rem;
        }

        .feature {
            border: 1px solid #f3e2d4;
            background: #fffcf8;
            border-radius: 14px;
            padding: .85rem;
            font-size: .92rem;
            color: #5c4238;
        }

        .card {
            padding: 1.6rem;
            align-self: center;
        }

        .card h2 {
            margin: 0 0 .35rem;
            font-family: "Sora", sans-serif;
            font-size: 1.45rem;
        }

        .card p {
            margin: 0 0 1.2rem;
            font-size: .95rem;
        }

        .field {
            margin-bottom: .95rem;
        }

        label {
            display: block;
            margin-bottom: .35rem;
            font-size: .92rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            border: 1px solid #ebd2c0;
            border-radius: 12px;
            padding: .8rem .95rem;
            font: inherit;
            color: var(--text);
            background: white;
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--ring);
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 12px;
            padding: .9rem 1rem;
            font: 700 1rem "Outfit", sans-serif;
            background: linear-gradient(180deg, var(--accent), var(--accent-dark));
            color: white;
            cursor: pointer;
        }

        .login-link {
            margin-top: .95rem;
            text-align: center;
            font-size: .9rem;
            color: #7a665e;
        }

        .login-link a {
            color: #b04707;
            text-decoration: none;
            font-weight: 700;
        }

        .errors {
            margin: 0 0 .9rem;
            padding: .75rem .9rem;
            border-radius: 10px;
            background: #fff2f2;
            color: #9f1239;
            border: 1px solid #fecdd3;
            font-size: .88rem;
        }

        .notice {
            margin-top: .8rem;
            display: flex;
            gap: .5rem;
            align-items: center;
            color: var(--ok);
            font-size: .86rem;
            font-weight: 600;
        }

        .powered-by {
            margin-top: .8rem;
            font-size: .82rem;
            color: #7a665e;
            text-align: center;
        }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <main class="layout">
        <section class="hero">
            <div>
                <p class="brand">StockPulse</p>
                <span class="eyebrow">Plano único • Sem pagamento agora</span>
                <h1>Precifique receitas com consistência e margem real.</h1>
                <p>
                    Crie sua conta gratuita com nome, email, contacto e senha. Em menos de 1 minuto você entra no painel e começa
                    a gerir ingredientes e receitas com custos isolados por utilizador.
                </p>
            </div>
            <div class="features">
                <div class="feature">Custos por ingrediente em meticais (MT).</div>
                <div class="feature">Preço por unidade com encargos e multiplicador.</div>
                <div class="feature">Dados isolados por conta para operação em equipa.</div>
            </div>
        </section>

        <section class="card">
            <h2>Criar conta</h2>
            <p>Sem cartão. Sem cobrança nesta fase.</p>

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

                <button class="btn" type="submit">Criar conta e entrar no painel</button>
            </form>

            <div class="login-link">
                Já tem acesso? <a href="{{ url('/admin/login') }}">Entrar</a>
            </div>

            <div class="notice">
                <span>●</span>
                <span>Ao cadastrar, você entra direto no dashboard.</span>
            </div>
            <div class="powered-by">Powered by Cheesemania</div>
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
