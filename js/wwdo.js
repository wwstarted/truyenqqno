// Toggle mở / đóng kết quả
document.querySelector(".bc-toggle").addEventListener("click", function () {
  document.getElementById("bc-result").classList.toggle("hidden");
});

// Xóc & tính toán
document.getElementById("bc-roll").addEventListener("click", function () {
  const inputs = document.querySelectorAll('input[name^="face"]');
  const values = {};

  inputs.forEach((input) => {
    const key = input.name.match(/\[(\d+)\]/)[1];
    values[key] = parseInt(input.value) || 0;
  });

  let minOutput = Infinity;
  let minCases = [];

  for (let a = 1; a <= 6; a++) {
    for (let b = 1; b <= 6; b++) {
      for (let c = 1; c <= 6; c++) {
        const output = values[a] + values[b] + values[c];
        const current = [a, b, c];

        if (output < minOutput) {
          minOutput = output;
          minCases = [current];
        } else if (output === minOutput) {
          minCases.push(current);
        }
      }
    }
  }

  const resultDiv = document.getElementById("bc-result");
  resultDiv.classList.remove("hidden");

  resultDiv.innerHTML = `
<p><strong>Output nhỏ nhất:</strong> ${minOutput}</p>
<p><strong>Số case đạt min:</strong> ${minCases.length}</p>
<p><strong>Ví dụ case:</strong></p>
<pre>${JSON.stringify(minCases.slice(0, 5), null, 2)}</pre>
`;
});
