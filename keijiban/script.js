// Small progressive enhancement for the classic bulletin board.
document.addEventListener('DOMContentLoaded', () => {
  const message = document.querySelector('#message');
  if (!message) return;
  message.addEventListener('input', () => {
    message.dataset.remaining = String(1000 - message.value.length);
  });
});
