const edit       = document.getElementById("edit");
const count_text = document.getElementById("count_text");
const text_size  = document.getElementById("text_size");
const font       = document.getElementById("font");

function applyTextSize() {
    if (!edit || !text_size) {
        return;
    }

    if (text_size.value === "minimum") {
        edit.style.fontSize = "11px";
    } else if (text_size.value === "medium") {
        edit.style.fontSize = "20px";
    } else if (text_size.value === "big") {
        edit.style.fontSize = "36px";
    }
}

function applyFont() {
    if (!edit || !font) {
        return;
    }

    if (font.value === "gothic") {
        edit.style.fontFamily = "sans-serif";
    } else if (font.value === "mincho") {
        edit.style.fontFamily = "serif";
    } else if (font.value === "m_plus") {
        edit.style.fontFamily = "'M PLUS 1P', sans-serif";
    }
}

function saveSettings() {
    if (text_size) {
        localStorage.setItem("memocho_text_size", text_size.value);
    }

    if (font) {
        localStorage.setItem("memocho_font", font.value);
    }
}

function loadSettings() {
    if (text_size) {
        const savedSize = localStorage.getItem("memocho_text_size");
        if (savedSize) {
            text_size.value = savedSize;
        }
    }

    if (font) {
        const savedFont = localStorage.getItem("memocho_font");
        if (savedFont) {
            font.value = savedFont;
        }
    }
}

function updateCount() {
    if (edit && count_text) {
        count_text.textContent = "文字数: " + edit.value.length;
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (!edit || !count_text) {
        return;
    }

    edit.value = localStorage.getItem("edit") || "";
    edit.addEventListener("input", updateCount);

    if (text_size) {
        text_size.addEventListener("change", () => {
            applyTextSize();
            saveSettings();
        });
    }

    if (font) {
        font.addEventListener("change", () => {
            applyFont();
            saveSettings();
        });
    }

    loadSettings();
    applyTextSize();
    applyFont();
    updateCount();
});

function saveText() {
    if (!edit) {
        return;
    }

    localStorage.setItem("edit", edit.value);
    saveSettings();
    alert("保存しました。");
}

function shareToTwitter() {
    if (!edit) {
        return;
    }

    const text = edit.value;
    const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(text);

    window.open(url, "_blank");
}