const edit         = document.getElementById("edit");
const count_text   = document.getElementById("count_text");
const text_size    = document.getElementById("text_size");
const font         = document.getElementById("font");
const setting      = document.getElementById("setting");
const closeSetting = document.getElementById("closeSetting");
const openSetting  = document.getElementById("openSetting");

// 險ｭ螳壹Δ繝ｼ繝繝ｫ縺ｮ陦ｨ遉ｺ繝ｻ髱櫁｡ｨ遉ｺ縺ｮ繧､繝吶Φ繝医Μ繧ｹ繝翫・繧定ｨｭ螳壹☆繧・
if (openSetting && setting && typeof setting.showModal === "function") {
    openSetting.addEventListener("click", () => {
        setting.showModal();
    });
}

if (closeSetting && setting) {
    closeSetting.addEventListener("click", () => {
        setting.close();
    });
}

// 譖ｸ蠑剰ｨｭ螳壹・邂・擅譖ｸ縺埼未騾｣
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
    } else if (font.value === "gothic_2") {
        edit.style.fontFamily = "'BIZ UDGothic', sans-serif";
    } else if (font.value === "mincho") {
        edit.style.fontFamily = "serif";
    } else if (font.value === "mincho_2") {
        edit.style.fontFamily = "'BIZ UDMincho', serif";
    } else if (font.value === "chinese") {
        edit.style.fontFamily = "'Microsoft YaHei Light', sans-serif";
    }
}

function setBulletPoints() {
    edit.value += "\u30fb\n\u30fb\n\u30fb";
    updateCount();
}

function setNumber() {
    edit.value += "1.\n2.\n3.";
    updateCount();
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
        count_text.textContent = "\u6587\u5b57\u6570: " + edit.value.length;
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
    alert("\u4fdd\u5b58\u3057\u307e\u3057\u305f\u3002");
}

function shareToTwitter() {
    if (!edit) {
        return;
    }

    const text = edit.value;
    const url = "https://twitter.com/intent/tweet?text=" + encodeURIComponent(text);

    window.open(url, "_blank");
}
