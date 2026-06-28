const edit = document.getElementById("edit");

edit.value = localStorage.getItem("edit") || "";

function saveText() {
    localStorage.setItem("edit", edit.value);
    alert("保存しました。");
}

function shareToTwitter() {
    const text = document.getElementById("edit").value;
    const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(text);

    window.open(url, "_blank");
}