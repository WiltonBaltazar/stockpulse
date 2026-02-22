<div
    style="
        border:1px solid #CEC7E5;
        background:#F0EFF4;
        border-radius:0.9rem;
        padding:0.75rem 0.9rem;
        margin-top:0.15rem;
    "
>
    <p
        style="
            margin:0;
            color:#685D94;
            font-size:0.75rem;
            font-weight:800;
            letter-spacing:0.08em;
            text-transform:uppercase;
            line-height:1.1;
        "
    >
        {{ $title }}
    </p>

    @if (filled($description))
        <p
            style="
                margin:0.22rem 0 0;
                color:#000000;
                font-size:0.88rem;
                font-weight:600;
                line-height:1.25;
            "
        >
            {{ $description }}
        </p>
    @endif
</div>
