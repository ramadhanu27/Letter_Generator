// script.js
const jenisSurat = document.getElementById("jenis-surat");
const inputFields = document.getElementById("input-fields");
const preview = document.getElementById("preview");
const generateBtn = document.getElementById("generate-btn");

// Preview gambar lampiran
const lampiranFile = document.getElementById("lampiran-file");
const lampiranPreview = document.getElementById("lampiran-preview");
let lampiranDataUrl = null;

const inputConfigs = {
  pernyataan: [
    { id: "nama", label: "Nama", type: "text" },
    { id: "tempatTanggalLahir", label: "Tempat/Tanggal Lahir", type: "text" },
    { id: "alamat", label: "Alamat", type: "text" },
    { id: "pernyataan", label: "Isi Pernyataan", type: "textarea" },
    { id: "tanggal", label: "Tanggal", type: "date" },
  ],
  izin: [
    { id: "nama", label: "Nama", type: "text" },
    { id: "kelas", label: "Kelas", type: "text" },
    { id: "keperluan", label: "Keperluan Izin", type: "textarea" },
    { id: "tanggal", label: "Tanggal", type: "date" },
  ],
  kuasa: [
    { id: "pemberi", label: "Nama Pemberi Kuasa", type: "text" },
    { id: "penerima", label: "Nama Penerima Kuasa", type: "text" },
    { id: "keperluan", label: "Keperluan", type: "textarea" },
    { id: "tanggal", label: "Tanggal", type: "date" },
  ],
};

const padLabel = (label, width = 22) => (label + ":").padEnd(width, " ");

const templates = {
  pernyataan: ({ nama, tempatTanggalLahir, alamat, pernyataan, tanggal }) =>
    `SURAT PERNYATAAN\n\nSaya yang bertanda tangan di bawah ini:\n${padLabel("Nama")}${nama}\n${padLabel("Tempat/Tanggal Lahir")}${tempatTanggalLahir}\n${padLabel(
      "Alamat"
    )}${alamat}\n\nDengan ini menyatakan bahwa:\n${pernyataan}\n\nDemikian surat pernyataan ini dibuat dengan sebenar-benarnya.\n\n${tanggal}\n\nHormat saya,\n\n${nama}`,
  izin: ({ nama, kelas, keperluan, tanggal }) =>
    `SURAT IZIN\n\nKepada Yth.\nBapak/Ibu Guru\nDi Tempat\n\nDengan hormat,\nSaya yang bertanda tangan di bawah ini:\n${padLabel("Nama")}${nama}\n${padLabel(
      "Kelas"
    )}${kelas}\n\nDengan ini memohon izin untuk ${keperluan}.\n\nDemikian surat ini saya buat, atas perhatian Bapak/Ibu saya ucapkan terima kasih.\n\n${tanggal}\n\nHormat saya,\n\n${nama}`,
  kuasa: ({ pemberi, penerima, keperluan, tanggal }) =>
    `SURAT KUASA\n\nYang bertanda tangan di bawah ini:\n${padLabel("Nama")}${pemberi}\n\nDengan ini memberikan kuasa kepada:\n${padLabel(
      "Nama"
    )}${penerima}\n\nUntuk keperluan: ${keperluan}\n\nDemikian surat kuasa ini dibuat untuk dipergunakan sebagaimana mestinya.\n\n${tanggal}\n\nPemberi Kuasa,\n\n${pemberi}`,
};

function renderInputs(type) {
  inputFields.innerHTML = "";
  inputConfigs[type].forEach((field) => {
    const wrapper = document.createElement("div");
    wrapper.className = "mb-2";
    const label = document.createElement("label");
    label.className = "block font-medium";
    label.textContent = field.label;
    let input;
    if (field.type === "textarea") {
      input = document.createElement("textarea");
      input.className = "w-full border rounded p-2";
      input.rows = 3;
    } else {
      input = document.createElement("input");
      input.type = field.type;
      input.className = "w-full border rounded p-2";
    }
    input.id = field.id;
    input.name = field.id;
    wrapper.appendChild(label);
    wrapper.appendChild(input);
    inputFields.appendChild(wrapper);
  });
}

function getInputValues(type) {
  const values = {};
  inputConfigs[type].forEach((field) => {
    const el = document.getElementById(field.id);
    values[field.id] = el.value;
    if (field.type === "date" && el.value) {
      // Format tanggal ke format Indonesia
      const date = new Date(el.value);
      values[field.id] = date.toLocaleDateString("id-ID", { day: "numeric", month: "long", year: "numeric" });
    }
  });
  return values;
}

function updatePreview() {
  const type = jenisSurat.value;
  const values = getInputValues(type);
  preview.textContent = templates[type](values);
}

jenisSurat.addEventListener("change", () => {
  renderInputs(jenisSurat.value);
  updatePreview();
  inputFields.querySelectorAll("input, textarea").forEach((input) => {
    input.addEventListener("input", updatePreview);
  });
});

document.addEventListener("DOMContentLoaded", () => {
  renderInputs(jenisSurat.value);
  updatePreview();
  inputFields.querySelectorAll("input, textarea").forEach((input) => {
    input.addEventListener("input", updatePreview);
  });
});

generateBtn.addEventListener("click", () => {
  const type = jenisSurat.value;
  const values = getInputValues(type);
  const doc = new window.jspdf.jsPDF();
  doc.setFont("Times", "Roman");
  doc.setFontSize(12);

  let y = 20;
  function addLine(text, indent = 0) {
    doc.text(text, 15 + indent, y);
    y += 8;
  }

  // Tentukan posisi X tetap untuk nilai setelah titik dua
  const LABELS = [
    "Nama",
    "Tempat/Tanggal Lahir",
    "Alamat",
    "Kelas",
    "Nama Pemberi Kuasa",
    "Nama Penerima Kuasa"
  ];
  const tempDoc = new window.jspdf.jsPDF();
  const maxLabelWidth = Math.max(...LABELS.map(l => tempDoc.getTextWidth(l + ':')));
  const valueX = 15 + maxLabelWidth + 5;

  function addLabelValue(label, value) {
    const labelText = label + ':';
    const labelX = 15;
    const maxValueWidth = 180 - valueX; // 180 = lebar halaman A4 jsPDF - margin
    if (doc.getTextWidth(value) > maxValueWidth) {
      // Nilai panjang, wrap ke bawah label
      doc.text(labelText, labelX, y);
      const lines = doc.splitTextToSize(value, 180 - labelX - 10);
      lines.forEach((line, idx) => {
        doc.text(line, labelX + 10, y + (idx === 0 ? 8 : 8 * (idx + 1)));
      });
      y += 8 * (lines.length + 1);
    } else {
      // Nilai pendek, tetap di kolom kanan
      doc.text(labelText, labelX, y);
      doc.text(value, valueX, y);
      y += 8;
    }
  }

  if (type === "pernyataan") {
    addLine("SURAT PERNYATAAN", 0);
    y += 4;
    addLine("");
    addLine("Saya yang bertanda tangan di bawah ini:");
    addLabelValue("Nama", values.nama);
    addLabelValue("Tempat/Tanggal Lahir", values.tempatTanggalLahir);
    addLabelValue("Alamat", values.alamat);
    y += 4;
    addLine("");
    addLine("Dengan ini menyatakan bahwa:");
    doc.text(values.pernyataan, 15, y);
    y += 12;
    addLine("");
    addLine("Demikian surat pernyataan ini dibuat dengan sebenar-benarnya.");
    y += 4;
    addLine("");
    if (values.tanggal && values.tanggal.trim() !== "") {
      addLine(values.tanggal);
      y += 8;
    }
    addLine("Hormat saya,");
    y += 16;
    if (values.nama && values.nama.trim() !== "") {
      addLine(values.nama);
    }
    // Tambah lampiran jika ada
    if (lampiranDataUrl) {
      y += 8;
      doc.text("Lampiran:", 15, y);
      y += 4;
      doc.addImage(lampiranDataUrl, "JPEG", 15, y, 50, 40);
      y += 44;
    }
  } else if (type === "izin") {
    addLine("SURAT IZIN", 0);
    y += 4;
    addLine("");
    addLine("Kepada Yth.");
    addLine("Bapak/Ibu Guru");
    addLine("Di Tempat");
    y += 4;
    addLine("");
    addLine("Dengan hormat,");
    addLine("Saya yang bertanda tangan di bawah ini:");
    addLabelValue("Nama", values.nama);
    addLabelValue("Kelas", values.kelas);
    y += 4;
    addLine("");
    addLine("Dengan ini memohon izin untuk " + values.keperluan + ".");
    y += 8;
    addLine("Demikian surat ini saya buat, atas perhatian Bapak/Ibu saya ucapkan terima kasih.");
    y += 4;
    addLine("");
    if (values.tanggal && values.tanggal.trim() !== "") {
      addLine(values.tanggal);
      y += 8;
    }
    addLine("Hormat saya,");
    y += 16;
    if (values.nama && values.nama.trim() !== "") {
      addLine(values.nama);
    }
    if (lampiranDataUrl) {
      y += 8;
      doc.text("Lampiran:", 15, y);
      y += 4;
      doc.addImage(lampiranDataUrl, "JPEG", 15, y, 50, 40);
      y += 44;
    }
  } else if (type === "kuasa") {
    addLine("SURAT KUASA", 0);
    y += 4;
    addLine("");
    addLine("Yang bertanda tangan di bawah ini:");
    addLabelValue("Nama", values.pemberi);
    y += 4;
    addLine("");
    addLine("Dengan ini memberikan kuasa kepada:");
    addLabelValue("Nama", values.penerima);
    y += 4;
    addLine("");
    addLine("Untuk keperluan:");
    doc.text(values.keperluan, 15, y);
    y += 12;
    addLine("");
    addLine("Demikian surat kuasa ini dibuat untuk dipergunakan sebagaimana mestinya.");
    y += 4;
    addLine("");
    if (values.tanggal && values.tanggal.trim() !== "") {
      addLine(values.tanggal);
      y += 8;
    }
    addLine("Pemberi Kuasa,");
    y += 16;
    if (values.pemberi && values.pemberi.trim() !== "") {
      addLine(values.pemberi);
    }
    if (lampiranDataUrl) {
      y += 8;
      doc.text("Lampiran:", 15, y);
      y += 4;
      doc.addImage(lampiranDataUrl, "JPEG", 15, y, 50, 40);
      y += 44;
    }
  }
  doc.save(`surat-${type}.pdf`);
});

if (lampiranFile) {
  lampiranFile.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (evt) {
        lampiranDataUrl = evt.target.result;
        lampiranPreview.innerHTML = `<img src="${lampiranDataUrl}" alt="Lampiran" class="max-h-40 mt-2" />`;
      };
      reader.readAsDataURL(file);
    } else {
      lampiranDataUrl = null;
      lampiranPreview.innerHTML = "";
    }
  });
}
