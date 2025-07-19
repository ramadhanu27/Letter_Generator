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
    { id: "nama", label: "Nama Lengkap", type: "text", placeholder: "Masukkan nama lengkap" },
    { id: "jabatan", label: "Jabatan/Posisi", type: "text", placeholder: "Contoh: Staff IT, Mahasiswa S1, Siswa Kelas XII" },
    { id: "instansi", label: "Instansi/Organisasi", type: "text", placeholder: "Contoh: PT Teknologi Maju, Universitas Indonesia, SMA Negeri 1" },
    { id: "alamatPenerima", label: "Ditujukan Kepada", type: "text", placeholder: "Contoh: Manager HRD, Dekan Fakultas Teknik, Kepala Sekolah" },
    { id: "instansiPenerima", label: "Nama Instansi Penerima", type: "text", placeholder: "Nama lengkap instansi/organisasi penerima" },
    { id: "keperluan", label: "Keperluan/Alasan Izin", type: "textarea", placeholder: "Contoh: Menghadiri acara pernikahan keluarga, Mengikuti seminar nasional, Sakit dan perlu istirahat" },
    { id: "tanggalMulai", label: "Tanggal Mulai Izin", type: "date" },
    { id: "tanggalSelesai", label: "Tanggal Selesai Izin", type: "date" },
    { id: "tanggal", label: "Tanggal Surat", type: "date" },
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
  izin: ({ nama, jabatan, instansi, alamatPenerima, instansiPenerima, keperluan, tanggalMulai, tanggalSelesai, tanggal }) => {
    const periode = tanggalMulai && tanggalSelesai ? (tanggalMulai === tanggalSelesai ? `pada tanggal ${tanggalMulai}` : `dari tanggal ${tanggalMulai} sampai dengan ${tanggalSelesai}`) : "";

    return `SURAT IZIN\n\nKepada Yth.\n${alamatPenerima || "Pimpinan"}\n${instansiPenerima || "Di Tempat"}\n\nDengan hormat,\nSaya yang bertanda tangan di bawah ini:\n${padLabel("Nama")}${nama}\n${padLabel(
      "Jabatan/Posisi"
    )}${jabatan}\n${padLabel(
      "Instansi/Organisasi"
    )}${instansi}\n\nDengan ini memohon izin ${periode} untuk ${keperluan}.\n\nDemikian surat ini saya buat, atas perhatian dan perkenan Bapak/Ibu saya ucapkan terima kasih.\n\n${tanggal}\n\nHormat saya,\n\n${nama}`;
  },
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
    icon.className = "mr-2 text-blue-500";
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
      input.className = "w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 resize-none shadow-sm hover:shadow-md bg-white";
      input.rows = 4;
      input.placeholder = field.placeholder || `Masukkan ${field.label.toLowerCase()}...`;
    } else {
      input = document.createElement("input");
      input.type = field.type;
      input.className = "w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm hover:shadow-md bg-white";
      if (field.type === "text") {
        input.placeholder = field.placeholder || `Masukkan ${field.label.toLowerCase()}...`;
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

  // Show/hide contoh buttons untuk surat izin
  const contohButtons = document.getElementById("contoh-buttons");
  if (jenisSurat.value === "izin") {
    contohButtons.classList.remove("hidden");
  } else {
    contohButtons.classList.add("hidden");
  }
});

// Contoh data untuk surat izin
const contohData = {
  karyawan: {
    nama: "Ahmad Wijaya",
    jabatan: "Staff IT",
    instansi: "PT Teknologi Maju",
    alamatPenerima: "Manager HRD",
    instansiPenerima: "PT Teknologi Maju",
    keperluan: "menghadiri acara pernikahan keluarga di luar kota",
    tanggalMulai: "2025-01-15",
    tanggalSelesai: "2025-01-17",
    tanggal: "2025-01-10",
  },
  mahasiswa: {
    nama: "Sari Indah Permata",
    jabatan: "Mahasiswa S1 Teknik Informatika",
    instansi: "Universitas Indonesia",
    alamatPenerima: "Dekan Fakultas Teknik",
    instansiPenerima: "Universitas Indonesia",
    keperluan: "mengikuti seminar nasional teknologi informasi yang diselenggarakan oleh IEEE Indonesia",
    tanggalMulai: "2025-01-20",
    tanggalSelesai: "2025-01-20",
    tanggal: "2025-01-15",
  },
  siswa: {
    nama: "Budi Santoso",
    jabatan: "Siswa Kelas XII IPA 1",
    instansi: "SMA Negeri 1 Jakarta",
    alamatPenerima: "Kepala Sekolah",
    instansiPenerima: "SMA Negeri 1 Jakarta",
    keperluan: "mengikuti olimpiade matematika tingkat provinsi DKI Jakarta",
    tanggalMulai: "2025-01-25",
    tanggalSelesai: "2025-01-25",
    tanggal: "2025-01-20",
  },
};

function fillContohData(type) {
  const data = contohData[type];
  if (!data) return;

  Object.keys(data).forEach((key) => {
    const input = document.getElementById(key);
    if (input) {
      input.value = data[key];
    }
  });

  updatePreview();
}

document.addEventListener("DOMContentLoaded", () => {
  renderInputs(jenisSurat.value);
  updatePreview();
  inputFields.querySelectorAll("input, textarea").forEach((input) => {
    input.addEventListener("input", updatePreview);
  });

  // Event listeners untuk tombol contoh
  document.getElementById("contoh-karyawan")?.addEventListener("click", () => fillContohData("karyawan"));
  document.getElementById("contoh-mahasiswa")?.addEventListener("click", () => fillContohData("mahasiswa"));
  document.getElementById("contoh-siswa")?.addEventListener("click", () => fillContohData("siswa"));
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

    // Validasi khusus untuk surat izin
    if (type === "izin") {
      if (values.tanggalMulai && values.tanggalSelesai) {
        const startDate = new Date(values.tanggalMulai);
        const endDate = new Date(values.tanggalSelesai);

        if (endDate < startDate) {
          alert("Tanggal selesai izin tidak boleh lebih awal dari tanggal mulai izin.");
          return;
        }
      }
    }

    const doc = new window.jspdf.jsPDF();
    doc.setFont("Times", "Roman");
    doc.setFontSize(12);

    // Konstanta untuk formatting standar surat resmi Indonesia
    const MARGIN_LEFT = 25; // 25mm margin kiri
    const MARGIN_RIGHT = 25; // 25mm margin kanan
    const MARGIN_TOP = 25; // 25mm margin atas
    const MARGIN_BOTTOM = 25; // 25mm margin bawah
    const LINE_HEIGHT = 5; // Tinggi baris standar
    const PARAGRAPH_SPACING = 6; // Jarak antar paragraf
    const SECTION_SPACING = 10; // Jarak antar bagian
    const PAGE_WIDTH = 210 - MARGIN_LEFT - MARGIN_RIGHT; // Lebar efektif halaman A4
    const PAGE_HEIGHT = 297 - MARGIN_TOP - MARGIN_BOTTOM; // Tinggi efektif halaman A4

    let y = MARGIN_TOP; // Posisi awal dari margin atas

    function addLine(text, indent = 0, spacing = LINE_HEIGHT, align = "left") {
      if (text.trim() === "") {
        y += spacing;
        return;
      }
      checkPageBreak(spacing + 5);

      let x = MARGIN_LEFT + indent;
      if (align === "center") {
        const textWidth = doc.getTextWidth(text);
        x = (210 - textWidth) / 2; // Center pada halaman A4
      } else if (align === "right") {
        const textWidth = doc.getTextWidth(text);
        x = 210 - MARGIN_RIGHT - textWidth;
      }

      doc.text(text, x, y);
      y += spacing;
    }

    function addParagraph(text, indent = 0, justify = false) {
      if (!text || text.trim() === "") return;

      const maxWidth = PAGE_WIDTH - indent - 10;
      const lines = doc.splitTextToSize(text, maxWidth);

      checkPageBreak(lines.length * LINE_HEIGHT + PARAGRAPH_SPACING + 10);

      lines.forEach((line) => {
        // Untuk paragraf yang di-justify, tambahkan spacing antar kata
        if (justify && lines.length > 1 && line !== lines[lines.length - 1]) {
          // Implementasi sederhana justify - bisa diperbaiki lebih lanjut
          doc.text(line, MARGIN_LEFT + indent, y);
        } else {
          doc.text(line, MARGIN_LEFT + indent, y);
        }
        y += LINE_HEIGHT;
      });
      y += PARAGRAPH_SPACING;
    }

    // Hitung lebar maksimal label untuk alignment yang konsisten
    const ALL_LABELS = ["Nama", "Tempat/Tanggal Lahir", "Alamat", "Kelas", "Jabatan/Posisi", "Instansi/Organisasi", "Nama Pemberi Kuasa", "Nama Penerima Kuasa", "Jabatan", "NIP"];

    // Gunakan font yang sama untuk mengukur
    doc.setFont("Times", "Roman");
    doc.setFontSize(12);

    const maxLabelWidth = Math.max(...ALL_LABELS.map((label) => doc.getTextWidth(label + " ")));
    const VALUE_START_X = MARGIN_LEFT + maxLabelWidth + 15; // Spacing yang cukup untuk titik dua

    function addLabelValue(label, value) {
      if (!value || value.trim() === "") return;

      checkPageBreak(LINE_HEIGHT + 10);

      const labelText = label;
      const colonText = ":";
      const maxValueWidth = PAGE_WIDTH - (VALUE_START_X - MARGIN_LEFT) - 10;

      // Cek apakah nilai terlalu panjang untuk satu baris
      if (doc.getTextWidth(value) > maxValueWidth) {
        // Label dan titik dua di baris pertama
        doc.text(labelText, MARGIN_LEFT, y);
        doc.text(colonText, MARGIN_LEFT + maxLabelWidth + 5, y);
        y += LINE_HEIGHT;

        // Nilai di baris berikutnya dengan indentasi yang rapi
        const lines = doc.splitTextToSize(value, PAGE_WIDTH - 30);
        lines.forEach((line) => {
          doc.text(line, VALUE_START_X, y);
          y += LINE_HEIGHT;
        });
        y += 2; // Spacing kecil antar item
      } else {
        // Label, titik dua, dan nilai dalam satu baris dengan alignment yang rapi
        doc.text(labelText, MARGIN_LEFT, y);
        doc.text(colonText, MARGIN_LEFT + maxLabelWidth + 5, y);
        doc.text(value, VALUE_START_X, y);
        y += LINE_HEIGHT + 2; // Spacing yang konsisten
      }
    }

    function addSection(spacing = SECTION_SPACING) {
      y += spacing;
    }

    function addTitle(title) {
      checkPageBreak(25); // Pastikan ruang cukup untuk judul
      doc.setFont("Times", "Bold");
      doc.setFontSize(16); // Ukuran font lebih besar untuk judul
      addLine(title, 0, LINE_HEIGHT + 5, "center");
      doc.setFont("Times", "Roman");
      doc.setFontSize(12);
      y += SECTION_SPACING; // Spacing setelah judul
    }

    function addDateLocation(date, location = "Jakarta") {
      if (!date || date.trim() === "") return;

      checkPageBreak(15);
      const dateText = `${location}, ${date}`;
      addLine(dateText, 0, LINE_HEIGHT, "right");
      y += SECTION_SPACING;
    }

    function addSalutation(text) {
      checkPageBreak(10);
      addLine(text, 0, LINE_HEIGHT);
      y += 3; // Spacing kecil setelah salam
    }

    function addClosing(name, title = "", customClosing = "") {
      checkPageBreak(60); // Ruang untuk tanda tangan

      // Kalimat penutup yang dapat dikustomisasi
      const closingText = customClosing || "Demikian surat ini dibuat dengan sebenar-benarnya.";
      addLine(closingText, 0, LINE_HEIGHT);
      y += SECTION_SPACING;

      addLine("Hormat saya,", 0, LINE_HEIGHT);
      y += 40; // Ruang untuk tanda tangan (3 baris standar)

      if (name && name.trim() !== "") {
        addLine(name, 0, LINE_HEIGHT);
        if (title && title.trim() !== "") {
          addLine(title, 0, LINE_HEIGHT);
        }
      }
    }

    function addLetterhead(title, subtitle = "") {
      // Fungsi untuk menambahkan kop surat jika diperlukan
      checkPageBreak(30);
      doc.setFont("Times", "Bold");
      doc.setFontSize(14);
      addLine(title, 0, LINE_HEIGHT, "center");

      if (subtitle && subtitle.trim() !== "") {
        doc.setFontSize(12);
        addLine(subtitle, 0, LINE_HEIGHT, "center");
      }

      doc.setFont("Times", "Roman");
      doc.setFontSize(12);

      // Garis pemisah
      y += 5;
      doc.line(MARGIN_LEFT, y, 210 - MARGIN_RIGHT, y);
      y += SECTION_SPACING;
    }

    function checkPageBreak(requiredSpace = 20) {
      if (y + requiredSpace > 270) {
        // 270 adalah mendekati bawah halaman A4
        doc.addPage();
        y = MARGIN_TOP; // Reset ke margin atas
      }
    }

    if (type === "pernyataan") {
      // Header dengan judul terpusat
      addTitle("SURAT PERNYATAAN");
      addSection();

      // Tanggal dan tempat (kanan atas)
      if (values.tanggal && values.tanggal.trim() !== "") {
        addDateLocation(values.tanggal, "Jakarta");
      }

      // Pembuka surat
      addSalutation("Saya yang bertanda tangan di bawah ini:");
      addSection(3);

      // Data diri dengan format label-value yang rapi
      addLabelValue("Nama", values.nama);
      addLabelValue("Tempat/Tanggal Lahir", values.tempatTanggalLahir);
      addLabelValue("Alamat", values.alamat);

      addSection();
      addLine("Dengan ini menyatakan bahwa:");
      addSection(5);

      // Isi pernyataan dengan justify
      addParagraph(values.pernyataan, 10, true);

      addSection();

      // Penutup dengan format standar untuk surat pernyataan
      addClosing(values.nama, "", "Demikian surat pernyataan ini dibuat dengan sebenar-benarnya.");

      // Tambah lampiran jika ada
      if (lampiranDataUrl) {
        addSection();
        addLine("Lampiran:");
        addSection(4);
        doc.addImage(lampiranDataUrl, "JPEG", MARGIN_LEFT, y, 50, 40);
        y += 44;
      }
    } else if (type === "izin") {
      // Header dengan judul terpusat
      addTitle("SURAT IZIN");
      addSection();

      // Tanggal dan tempat (kanan atas)
      if (values.tanggal && values.tanggal.trim() !== "") {
        addDateLocation(values.tanggal, "Jakarta");
      }

      // Alamat surat yang fleksibel
      addLine("Kepada Yth.");
      addLine(values.alamatPenerima || "Pimpinan");
      addLine(values.instansiPenerima || "Di Tempat");

      addSection();

      // Salam pembuka
      addSalutation("Dengan hormat,");
      addLine("Saya yang bertanda tangan di bawah ini:");
      addSection(5);

      // Data diri pemohon yang fleksibel
      addLabelValue("Nama", values.nama);
      addLabelValue("Jabatan/Posisi", values.jabatan);
      addLabelValue("Instansi/Organisasi", values.instansi);

      addSection();

      // Isi permohonan izin dengan periode yang fleksibel
      let periodeText = "";
      if (values.tanggalMulai && values.tanggalSelesai) {
        if (values.tanggalMulai === values.tanggalSelesai) {
          periodeText = ` pada tanggal ${values.tanggalMulai}`;
        } else {
          periodeText = ` dari tanggal ${values.tanggalMulai} sampai dengan ${values.tanggalSelesai}`;
        }
      }

      const permohonanText = `Dengan ini memohon izin${periodeText} untuk ${values.keperluan}.`;
      addParagraph(permohonanText, 10, true);

      addSection();

      // Penutup dengan format standar untuk surat izin yang universal
      addClosing(values.nama, values.jabatan, "Demikian surat ini saya buat, atas perhatian dan perkenan Bapak/Ibu saya ucapkan terima kasih.");

      if (lampiranDataUrl) {
        addSection();
        addLine("Lampiran:");
        addSection(4);
        doc.addImage(lampiranDataUrl, "JPEG", MARGIN_LEFT, y, 50, 40);
        y += 44;
      }
    } else if (type === "kuasa") {
      // Header dengan judul terpusat
      addTitle("SURAT KUASA");
      addSection();

      // Tanggal dan tempat (kanan atas)
      if (values.tanggal && values.tanggal.trim() !== "") {
        addDateLocation(values.tanggal, "Jakarta");
      }

      // Pembuka surat kuasa
      addSalutation("Yang bertanda tangan di bawah ini:");
      addSection(5);

      // Data pemberi kuasa
      addLabelValue("Nama", values.pemberi);

      addSection();
      addLine("Dengan ini memberikan kuasa kepada:");
      addSection(5);

      // Data penerima kuasa
      addLabelValue("Nama", values.penerima);

      addSection();
      addLine("Untuk keperluan:");
      addSection(5);

      // Isi keperluan dengan format yang rapi
      addParagraph(values.keperluan, 10, true);

      addSection();
      addLine("Demikian surat kuasa ini dibuat untuk dipergunakan sebagaimana mestinya.");

      addSection();

      // Penutup khusus untuk surat kuasa
      checkPageBreak(60);
      addLine("Pemberi Kuasa,", 0, LINE_HEIGHT);
      y += 40; // Ruang untuk tanda tangan

      if (values.pemberi && values.pemberi.trim() !== "") {
        addLine(values.pemberi, 0, LINE_HEIGHT);
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
          <div class="text-center p-4">
            <img src="${lampiranDataUrl}" alt="Lampiran" class="max-h-40 mx-auto rounded-lg shadow-md border border-blue-200" />
            <p class="text-sm text-gray-600 mt-3 font-medium">${file.name}</p>
            <button type="button" onclick="removeAttachment()" class="inline-flex items-center text-red-500 text-sm hover:text-red-700 mt-2 px-3 py-1 rounded-lg hover:bg-red-50 transition-colors duration-200">
              <i class="fas fa-trash mr-1"></i>Hapus Lampiran
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
