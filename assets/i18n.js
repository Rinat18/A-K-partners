// Многоязычность: русский — основной, кыргызский и английский добавляются позже.
// Как добавить перевод: заполните нужный язык ниже ключами из data-i18n="..." в index.html.
// Языки без переводов не показываются в переключателе. Ненайденный ключ показывается по-русски.
(function () {
  var T = {
    ru: {},          // русский текст лежит прямо в index.html
    ky: {},          // TODO: кыргызский — {'nav.about': 'Биз жөнүндө', ...}
    en: {}           // TODO: английский — {'nav.about': 'About', ...}
  };
  var LABEL = { ru: 'RU', ky: 'KG', en: 'EN' };
  var enabled = ['ru'].concat(['ky', 'en'].filter(function (l) { return Object.keys(T[l]).length; }));
  var ru = {};
  document.querySelectorAll('[data-i18n]').forEach(function (el) { ru[el.dataset.i18n] = el.innerHTML; });

  function apply(lang) {
    document.documentElement.lang = lang === 'ky' ? 'ky' : lang;
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var k = el.dataset.i18n;
      el.innerHTML = (lang !== 'ru' && T[lang][k]) || ru[k];
    });
    try { localStorage.setItem('lang', lang); } catch (e) {}
  }

  if (enabled.length < 2) return; // переводов пока нет — переключатель не показываем
  var box = document.getElementById('lang-switch');
  if (!box) return;
  box.style.display = '';
  enabled.forEach(function (l) {
    var b = document.createElement('button');
    b.type = 'button'; b.textContent = LABEL[l]; b.onclick = function () { apply(l); };
    box.appendChild(b);
  });
  var saved; try { saved = localStorage.getItem('lang'); } catch (e) {}
  if (saved && enabled.indexOf(saved) > 0) apply(saved);
})();
