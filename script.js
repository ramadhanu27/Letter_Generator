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
  inputConfigs[type].forEach((field, index) => {
    const wrapper = document.createElement("div");
    wrapper.className = "space-y-3 animate-slide-up";
    wrapper.style.animationDelay = `${index * 0.1}s`;

    const label = document.createElement("label");
    label.className = "flex items-center text-sm font-medium text-gray-700";

    // Add icons for different field types
    const icon = document.createElement("i");
    icon.className = "mr-2 text-gray-400";
    switch (field.type) {
      case "text":
        if (field.id.includes("nama")) {
          icon.className += " fas fa-user";
        } else if (field.id.includes("alamat")) {
          icon.className += " fas fa-map-marker-alt";
        } else if (field.id.includes("kelas")) {
          icon.className += " fas fa-graduation-cap";
        } else if (field.id.includes("tempat")) {
          icon.className += " fas fa-birthday-cake";
        } else {
          icon.className += " fas fa-edit";
        }
        break;
      case "textarea":
        icon.className += " fas fa-align-left";
        break;
      case "date":
        icon.className += " fas fa-calendar-alt";
        break;
      default:
        icon.className += " fas fa-edit";
    }

    label.appendChild(icon);
    label.appendChild(document.createTextNode(field.label));

    let input;
    if (field.type === "textarea") {
      input = document.createElement("textarea");
      input.className = "form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none";
      input.rows = 4;
      input.placeholder = `Masukkan ${field.label.toLowerCase()}...`;
    } else {
      input = document.createElement("input");
      input.type = field.type;
      input.className = "form-input w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200";
      if (field.type === "text") {
        input.placeholder = `Masukkan ${field.label.toLowerCase()}...`;
      }
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
  const previewText = templates[type](values);

  // Check if all fields are empty
  const hasContent = Object.values(values).some((value) => value && value.trim() !== "");

  if (!hasContent) {
    preview.innerHTML = `
      <div class="text-gray-400 text-center py-8">
        <i class="fas fa-file-alt text-4xl mb-4"></i>
        <p>Preview surat akan muncul di sini setelah Anda mengisi form</p>
      </div>
    `;
  } else {
    preview.textContent = previewText;
  }
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

generateBtn.addEventListener("click", async () => {
  // Show loading state
  const btnText = document.getElementById("btn-text");
  const loadingSpinner = document.getElementById("loading-spinner");

  btnText.textContent = "Generating...";
  loadingSpinner.classList.remove("hidden");
  generateBtn.disabled = true;
  generateBtn.classList.add("opacity-75", "cursor-not-allowed");

  try {
    // Add small delay for better UX
    await new Promise((resolve) => setTimeout(resolve, 500));

    const type = jenisSurat.value;
    const values = getInputValues(type);

    // Validate required fields
    const requiredFields = inputConfigs[type].filter((field) => field.type !== "file");
    const emptyFields = requiredFields.filter((field) => !values[field.id] || values[field.id].trim() === "");

    if (emptyFields.length > 0) {
      alert(`Mohon lengkapi field: ${emptyFields.map((f) => f.label).join(", ")}`);
      return;
    }

    const doc = new window.jspdf.jsPDF();
    doc.setFont("Times", "Roman");
    doc.setFontSize(12);

    // Konstanta untuk formatting yang konsisten
    const MARGIN_LEFT = 20;
    const LINE_HEIGHT = 6;
    const PARAGRAPH_SPACING = 4;
    const SECTION_SPACING = 8;
    const PAGE_WIDTH = 190; // A4 width minus margins

    let y = 25; // Starting position

    function addLine(text, indent = 0, spacing = LINE_HEIGHT) {
      if (text.trim() === "") {
        y += spacing;
        return;
      }
      checkPageBreak(spacing + 5);
      doc.text(text, MARGIN_LEFT + indent, y);
      y += spacing;
    }

    function addParagraph(text, indent = 0) {
      if (!text || text.trim() === "") return;

      const maxWidth = PAGE_WIDTH - MARGIN_LEFT - indent - 10;
      const lines = doc.splitTextToSize(text, maxWidth);

      checkPageBreak(lines.length * LINE_HEIGHT + PARAGRAPH_SPACING + 10);

      lines.forEach((line, index) => {
        doc.text(line, MARGIN_LEFT + indent, y);
        y += LINE_HEIGHT;
      });
      y += PARAGRAPH_SPACING + 2; // Extra spacing after paragraphs
    }

    // Hitung lebar maksimal label untuk alignment yang konsisten
    const ALL_LABELS = ["Nama", "Tempat/Tanggal Lahir", "Alamat", "Kelas", "Nama Pemberi Kuasa", "Nama Penerima Kuasa"];

    // Gunakan font yang sama untuk mengukur
    doc.setFont("Times", "Roman");
    doc.setFontSize(12);

    const maxLabelWidth = Math.max(...ALL_LABELS.map((label) => doc.getTextWidth(label + ":")));
    const VALUE_START_X = MARGIN_LEFT + maxLabelWidth + 8; // 8px spacing after colon

    function addLabelValue(label, value) {
      if (!value || value.trim() === "") return;

      const labelText = label + ":";
      const maxValueWidth = PAGE_WIDTH - VALUE_START_X - 10;

      // Cek apakah nilai terlalu panjang untuk satu baris
      if (doc.getTextWidth(value) > maxValueWidth) {
        // Label di baris terpisah, nilai di bawahnya dengan indentasi
        doc.text(labelText, MARGIN_LEFT, y);
        y += LINE_HEIGHT + 1;

        const lines = doc.splitTextToSize(value, PAGE_WIDTH - MARGIN_LEFT - 20);
        lines.forEach((line, index) => {
          doc.text(line, MARGIN_LEFT + 20, y);
          y += LINE_HEIGHT;
        });
        y += PARAGRAPH_SPACING;
      } else {
        // Label dan nilai dalam satu baris dengan alignment yang rapi
        doc.text(labelText, MARGIN_LEFT, y);
        doc.text(value, VALUE_START_X, y);
        y += LINE_HEIGHT + 3; // Spacing yang konsisten untuk readability
      }
    }

    function addSection(spacing = SECTION_SPACING) {
      y += spacing;
    }

    function addTitle(title) {
      checkPageBreak(20); // Ensure enough space for title
      doc.setFont("Times", "Bold");
      doc.setFontSize(14);
      const titleWidth = doc.getTextWidth(title);
      const centerX = (210 - titleWidth) / 2; // Center on A4 page
      doc.text(title, centerX, y);
      doc.setFont("Times", "Roman");
      doc.setFontSize(12);
      y += LINE_HEIGHT + SECTION_SPACING;
    }

    function checkPageBreak(requiredSpace = 20) {
      if (y + requiredSpace > 280) {
        // 280 is near bottom of A4 page
        doc.addPage();
        y = 25; // Reset to top margin
      }
    }

    if (type === "pernyataan") {
      addTitle("SURAT PERNYATAAN");
      addSection();

      addLine("Saya yang bertanda tangan di bawah ini:");
      addSection(4);

      addLabelValue("Nama", values.nama);
      addLabelValue("Tempat/Tanggal Lahir", values.tempatTanggalLahir);
      addLabelValue("Alamat", values.alamat);

      addSection();
      addLine("Dengan ini menyatakan bahwa:");
      addSection(4);

      addParagraph(values.pernyataan, 0);

      addSection();
      addLine("Demikian surat pernyataan ini dibuat dengan sebenar-benarnya.");

      addSection();
      if (values.tanggal && values.tanggal.trim() !== "") {
        addLine(values.tanggal);
        addSection(4);
      }

      addLine("Hormat saya,");
      addSection(20); // Space for signature

      if (values.nama && values.nama.trim() !== "") {
        addLine(values.nama);
      }

      // Tambah lampiran jika ada
      if (lampiranDataUrl) {
        addSection();
        addLine("Lampiran:");
        addSection(4);
        doc.addImage(lampiranDataUrl, "JPEG", MARGIN_LEFT, y, 50, 40);
        y += 44;
      }
    } else if (type === "izin") {
      addTitle("SURAT IZIN");
      addSection();

      addLine("Kepada Yth.");
      addLine("Bapak/Ibu Guru");
      addLine("Di Tempat");

      addSection();
      addLine("Dengan hormat,");
      addLine("Saya yang bertanda tangan di bawah ini:");
      addSection(4);

      addLabelValue("Nama", values.nama);
      addLabelValue("Kelas", values.kelas);

      addSection();
      addParagraph("Dengan ini memohon izin untuk " + values.keperluan + ".");

      addSection();
      addLine("Demikian surat ini saya buat, atas perhatian Bapak/Ibu saya ucapkan terima kasih.");

      addSection();
      if (values.tanggal && values.tanggal.trim() !== "") {
        addLine(values.tanggal);
        addSection(4);
      }

      addLine("Hormat saya,");
      addSection(20); // Space for signature

      if (values.nama && values.nama.trim() !== "") {
        addLine(values.nama);
      }

      if (lampiranDataUrl) {
        addSection();
        addLine("Lampiran:");
        addSection(4);
        doc.addImage(lampiranDataUrl, "JPEG", MARGIN_LEFT, y, 50, 40);
        y += 44;
      }
    } else if (type === "kuasa") {
      addTitle("SURAT KUASA");
      addSection();

      addLine("Yang bertanda tangan di bawah ini:");
      addSection(4);

      addLabelValue("Nama", values.pemberi);

      addSection();
      addLine("Dengan ini memberikan kuasa kepada:");
      addSection(4);

      addLabelValue("Nama", values.penerima);

      addSection();
      addLine("Untuk keperluan:");
      addSection(4);

      addParagraph(values.keperluan, 0);

      addSection();
      addLine("Demikian surat kuasa ini dibuat untuk dipergunakan sebagaimana mestinya.");

      addSection();
      if (values.tanggal && values.tanggal.trim() !== "") {
        addLine(values.tanggal);
        addSection(4);
      }

      addLine("Pemberi Kuasa,");
      addSection(20); // Space for signature

      if (values.pemberi && values.pemberi.trim() !== "") {
        addLine(values.pemberi);
      }

      if (lampiranDataUrl) {
        addSection();
        addLine("Lampiran:");
        addSection(4);
        doc.addImage(lampiranDataUrl, "JPEG", MARGIN_LEFT, y, 50, 40);
        y += 44;
      }
    }
    doc.save(`surat-${type}.pdf`);
  } catch (error) {
    console.error("Error generating PDF:", error);
    alert("Terjadi kesalahan saat membuat PDF. Silakan coba lagi.");
  } finally {
    // Reset loading state
    btnText.textContent = "Generate PDF";
    loadingSpinner.classList.add("hidden");
    generateBtn.disabled = false;
    generateBtn.classList.remove("opacity-75", "cursor-not-allowed");
  }
});

if (lampiranFile) {
  lampiranFile.addEventListener("change", function (e) {
    const file = e.target.files[0];
    if (file) {
      // Validate file type
      if (!file.type.startsWith("image/")) {
        alert("Mohon pilih file gambar yang valid (JPG, PNG, GIF, dll.)");
        lampiranFile.value = "";
        return;
      }

      // Validate file size (max 5MB)
      if (file.size > 5 * 1024 * 1024) {
        alert("Ukuran file terlalu besar. Maksimal 5MB.");
        lampiranFile.value = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = function (evt) {
        lampiranDataUrl = evt.target.result;
        lampiranPreview.classList.remove("hidden");
        lampiranPreview.innerHTML = `
          <div class="text-center">
            <img src="${lampiranDataUrl}" alt="Lampiran" class="max-h-40 mx-auto rounded-lg shadow-md" />
            <p class="text-sm text-gray-600 mt-2">${file.name}</p>
            <button type="button" onclick="removeAttachment()" class="text-red-500 text-sm hover:text-red-700 mt-1">
              <i class="fas fa-trash mr-1"></i>Hapus
            </button>
          </div>
        `;
      };
      reader.readAsDataURL(file);
    } else {
      removeAttachment();
    }
  });
}

function removeAttachment() {
  lampiranDataUrl = null;
  lampiranPreview.innerHTML = "";
  lampiranPreview.classList.add("hidden");
  lampiranFile.value = "";
}
