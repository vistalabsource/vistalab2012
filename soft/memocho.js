const edit = document.getElementById("edit");

edit.value = localStorage.getItem("edit") || "";

function saveText() {
    localStorage.setItem("edit", edit.value);
    alert("保存しました。");
}