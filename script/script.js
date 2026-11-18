const omikujiResult = document.getElementById("omikuji-result");

window.onload = () => {
    // ページが読み込まれたときに6種類のおみくじの結果をランダムに表示する
    const results = ["大吉", "中吉", "小吉", "吉", "末吉", "凶"];
    const randomIndex = Math.floor(Math.random() * results.length);
    omikujiResult.textContent = results[randomIndex];
}