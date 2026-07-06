/* 
    邁｡譏薙Γ繝｢蟶ｳ繝・く繧ｹ繝医ヵ繧｡繧､繝ｫ繝繧ｦ繝ｳ繝ｭ繝ｼ繝
*/


function downloadText() {
    const edit = document.getElementById("edit");
    if (!edit || !edit.value) {
        alert("\u30a8\u30c7\u30a3\u30bf\u306b\u4f55\u3082\u66f8\u304b\u308c\u3066\u3044\u307e\u305b\u3093\u3002");
        return;
    }

    const text = edit.value;

    const blob = new Blob([text], { type: "text/plain" });
    const url = URL.createObjectURL(blob);

    // 荳譎ら噪縺ｫ繝ｪ繝ｳ繧ｯ繧剃ｽ懈・縺励※繝繧ｦ繝ｳ繝ｭ繝ｼ繝峨Μ繝ｳ繧ｯ繧偵け繝ｪ繝・け縺吶ｋ
    const a = document.createElement("a");
    a.href = url;

    a.download = "memo.txt";

    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    // URL繧ｪ繝悶ず繧ｧ繧ｯ繝医ｒ隗｣謾ｾ縺励※繝｡繝｢繝ｪ繧定ｧ｣謾ｾ縺吶ｋ
    URL.revokeObjectURL(url);
} 
