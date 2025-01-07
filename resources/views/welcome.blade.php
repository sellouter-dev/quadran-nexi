<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quadran - Nexi API</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}" sizes="32x32"/>


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300..700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles to integrate with Tailwind */
        .font-press-start {
            font-family: 'Press Start 2P', cursive;
        }

        .font-fira-code {
            font-family: 'Fira Code', monospace;
        }

        .bg-svg {
            background: url('data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="none" stroke="rgb(148 163 184 / 0.05)"%3e%3cpath d="M0 .5H31.5V32"/%3e%3c/svg%3e') center center;
            mask-image: linear-gradient(to bottom, transparent, black);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black);
        }

        body {
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(43, 43, 43, 0.7)), url('data:image/svg+xml,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="none" stroke="rgb(148 163 184 / 0.05)"%3e%3cpath d="M0 .5H31.5V32"/%3e%3c/svg%3e') center center;
            background-size: cover;
        }

        pre {
            overflow-x: auto;
        }
    </style>
</head>

<body
    class="bg-gradient-to-b from-black to-lime-950 text-white font-sans antialiased min-h-screen flex flex-col relative">

    <!-- Background SVG -->
    <div class="absolute inset-0 bg-svg z-[-1]"></div>

    <!-- Header Section -->
    <header class="flex flex-col items-center justify-center text-center p-6 md:p-10 flex-grow-[0.5]">
        <h1 class="text-highlight text-4xl md:text-5xl font-bold mb-4 font-press-start text-[#70B80C]">Quadran API</h1>
        <p class="text-lg md:text-xl mb-8 font-fira-code text-white w-full md:w-3/4 lg:w-1/2">
            Le API di Quadran offrono un'interfaccia robusta e sicura per interagire con i nostri servizi.
            Progettate per essere intuitive e facili da usare, le API consentono di integrare rapidamente
            le funzionalità di Quadran nelle loro applicazioni, fornendo gli strumenti necessari per ottenere il massimo
            dai nostri servizi.
        </p>
        <a href="/api/documentation" target="_blank" rel="noopener noreferrer"
            class="bg-[#84CC16] hover:bg-[#70B80C] text-white font-semibold font-fira-code py-3 px-6 rounded-lg transition duration-300">Documentazione
            API</a>
    </header>

    <!-- Documentation Section -->
    <div class="bg-[#18181b] text-white p-6 md:p-8 m-6 rounded-lg font-fira-code w-full md:w-3/4 lg:w-1/2 mx-auto">
        <h2 class="text-2xl md:text-3xl mb-4 md:mb-6 text-[#70B80C]">Come accedere alle API</h2>
        <p class="mb-4">Per accedere alle API, è necessario effettuare una richiesta POST all'endpoint seguente:</p>
        <pre class="bg-zinc-800 p-4 rounded-lg mb-4 md:mb-6"><code>POST /api/login</code></pre>
        <p class="mb-4">Il corpo della richiesta deve includere i seguenti parametri:</p>
        <ul class="list-disc list-inside mb-4">
            <li><strong>email:</strong> il tuo indirizzo email</li>
            <li><strong>password:</strong> la tua password</li>
        </ul>
        <p class="mb-4">Esempio di corpo della richiesta in formato JSON:</p>
        <pre class="bg-zinc-800 p-4 rounded-lg mb-4 md:mb-6"><code>{
    "email": "example@example.com",
    "password": "yourpassword"
}</code></pre>
        <p class="mb-4">Una volta effettuato con successo il login, riceverai un token che dovrà essere utilizzato
            nell'intestazione Authorization per le richieste successive.</p>
        <p class="mb-4">Per utilizzare Swagger, segui questi passaggi:</p>
        <ul class="list-disc list-inside mb-4">
            <li>Apri Swagger e clicca sulla chiamata API per il login.</li>
            <li>Inserisci il corpo della richiesta di esempio e invia la richiesta.</li>
            <li>Prendi il token generato dalla risposta, che avrà la seguente struttura:</li>
        </ul>
        <p class="mb-4">Esempio di risposta JSON:</p>
        <pre class="bg-zinc-800 p-4 rounded-lg mb-4 md:mb-6"><code>{
    "message": "Login successful",
    "token": "12|G5vLsCdO4C3hue2SU0Q4biv9o77S7xvGYk6Iv6iC939fa413"
}</code></pre>
        <p class="mb-4">Successivamente, clicca sul bottone in alto a destra con scritto "Authorize".</p>
        <p class="mb-4">Inserisci il token nell'input "Value" e clicca il pulsante "Authorize" in verde, posto sotto
            l'input.</p>
    </div>

    <!-- Footer Section -->
    <footer class="text-center p-4 mt-auto">
        <p class="text-sm md:text-base text-white font-fira-code">&copy; 2024 Quadran. All rights reserved.</p>
    </footer>
</body>

</html>
