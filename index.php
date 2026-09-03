<?php ?><!doctype html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fahim's WebMail Server</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
  <div class="bg"></div>
  <div class="orb orb1"></div>
  <div class="orb orb2"></div>

  <main class="wrap">
    <section class="card glass">
      <header class="head">
        <div>
          <h1>Fahim's WebMail Server</h1>
          <p class="sub">Brevo-powered modern mail sender</p>
        </div>
        <button id="themeToggle" class="btn ghost" type="button" aria-label="Toggle theme">🌙 / ☀️</button>
      </header>

      <form id="mailForm" enctype="multipart/form-data" novalidate>
        <div class="row two">
          <label>
            Sender Name
            <input name="sender_name" type="text" placeholder="Fahim" required maxlength="70">
          </label>

          <label>
            Sender Alias
            <input name="sender_alias" type="text" placeholder="support" required pattern="^[a-zA-Z0-9._-]{2,40}$">
            <small>Will send as: <b>alias@fahim.pro.bd</b></small>
          </label>
        </div>

        <label>
          Recipient Email
          <input name="to" type="email" placeholder="someone@example.com" required>
        </label>

        <label>
          Subject
          <input name="subject" type="text" placeholder="Your subject..." maxlength="150" required>
        </label>

        <label>
          HTML Email Body
          <textarea name="message" rows="9" placeholder="<h2>Hello</h2><p>This is a modern email.</p>" required></textarea>
        </label>

        <label>
          Attachment (optional, max 10MB)
          <input name="attachment" type="file">
        </label>

        <div class="actions">
          <button id="sendBtn" class="btn primary" type="submit">
            <span class="btnText">Send Email</span>
            <span class="spinner hidden"></span>
          </button>
        </div>

        <p id="status" class="status" aria-live="polite"></p>
      </form>
    </section>
  </main>

  <div id="toast" class="toast hidden"></div>

  <div id="loadingOverlay" class="overlay hidden" aria-hidden="true">
    <div class="loaderCard">
      <div class="ring"></div>
      <p>Sending your email...</p>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>