const form = document.getElementById('mailForm');
const sendBtn = document.getElementById('sendBtn');
const spinner = sendBtn.querySelector('.spinner');
const btnText = sendBtn.querySelector('.btnText');
const statusEl = document.getElementById('status');
const toast = document.getElementById('toast');
const overlay = document.getElementById('loadingOverlay');
const themeToggle = document.getElementById('themeToggle');

themeToggle.addEventListener('click', () => {
  const html = document.documentElement;
  html.dataset.theme = html.dataset.theme === 'dark' ? 'light' : 'dark';
  localStorage.setItem('theme', html.dataset.theme);
});
(() => {
  const t = localStorage.getItem('theme');
  if (t === 'light' || t === 'dark') document.documentElement.dataset.theme = t;
})();

function setLoading(on){
  sendBtn.disabled = on;
  spinner.classList.toggle('hidden', !on);
  overlay.classList.toggle('hidden', !on);
  btnText.textContent = on ? 'Sending...' : 'Send Email';
}

function notify(message, type='success'){
  toast.textContent = message;
  toast.className = `toast ${type}`;
  toast.classList.remove('hidden');
  setTimeout(() => toast.classList.add('hidden'), 3200);
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  statusEl.textContent = '';
  setLoading(true);

  try{
    const fd = new FormData(form);
    const res = await fetch('send.php', { method:'POST', body: fd });
    const data = await res.json();

    if (data.ok){
      statusEl.textContent = '✅ ' + (data.message || 'Email sent successfully.');
      notify(data.message || 'Email sent successfully', 'success');
      form.reset();
    } else {
      statusEl.textContent = '❌ ' + (data.message || 'Sending failed.');
      notify(data.message || 'Sending failed', 'error');
    }
  } catch (err){
    statusEl.textContent = '❌ Network/server error.';
    notify('Network/server error', 'error');
  } finally {
    setLoading(false);
  }
});