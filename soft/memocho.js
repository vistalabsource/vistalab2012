const edit = document.getElementById("edit");
const count_text = document.getElementById("count_text");

edit.value = localStorage.getItem("edit") || "";

edit.addEventListener('input', () => {
   count_text.textContent = "文字数: " + edit.value.length;
});

function saveText() {
    localStorage.setItem("edit", edit.value);
    alert("保存しました。");
}

function shareToTwitter() {
    const text = document.getElementById("edit").value;
    const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(text);

    window.open(url, "_blank");
}