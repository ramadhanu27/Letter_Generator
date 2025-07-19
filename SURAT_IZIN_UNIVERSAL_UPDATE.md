# 🔄 **Update Surat Izin Universal - Template Fleksibel**

## 📋 **Ringkasan Perubahan**

Template "Surat Izin" telah dimodifikasi menjadi **universal dan fleksibel** untuk mengakomodasi berbagai konteks formal di Indonesia, tidak hanya terbatas untuk siswa sekolah. Template baru ini dapat digunakan oleh:

- 👔 **Karyawan** - untuk cuti atau izin dari atasan/HRD
- 🎓 **Mahasiswa** - untuk izin akademik dari dosen/dekan
- 📚 **Siswa** - untuk izin dari guru/kepala sekolah  
- 🏢 **Profesional** - untuk berbagai keperluan izin formal lainnya

## 🆕 **Field Input Baru**

### **Sebelum (Khusus Siswa):**
```
- Nama
- Kelas  
- Keperluan Izin
- Tanggal
```

### **Sesudah (Universal):**
```
- Nama Lengkap
- Jabatan/Posisi (Karyawan, Mahasiswa, Siswa, dll)
- Instansi/Organisasi (PT ABC, Universitas XYZ, SMA ABC)
- Ditujukan Kepada (Manager HRD, Dekan, Kepala Sekolah)
- Nama Instansi Penerima
- Keperluan/Alasan Izin (textarea detail)
- Tanggal Mulai Izin
- Tanggal Selesai Izin  
- Tanggal Surat
```

## 📝 **Format Surat Baru**

### **Template Universal:**
```
SURAT IZIN
                                        Jakarta, [Tanggal Surat]

Kepada Yth.
[Ditujukan Kepada]
[Nama Instansi Penerima]

Dengan hormat,
Saya yang bertanda tangan di bawah ini:

Nama                    : [Nama Lengkap]
Jabatan/Posisi          : [Jabatan/Posisi]
Instansi/Organisasi     : [Instansi/Organisasi]

Dengan ini memohon izin [periode] untuk [keperluan detail].

Demikian surat ini saya buat, atas perhatian dan perkenan 
Bapak/Ibu saya ucapkan terima kasih.

Hormat saya,


[Nama Lengkap]
[Jabatan/Posisi]
```

## 🎯 **Contoh Penggunaan**

### **1. Karyawan Kantor:**
```
Kepada Yth.
Manager HRD
PT Teknologi Maju

Nama                    : Ahmad Wijaya
Jabatan/Posisi          : Staff IT
Instansi/Organisasi     : PT Teknologi Maju

Dengan ini memohon izin dari tanggal 15 Januari 2025 
sampai dengan 17 Januari 2025 untuk keperluan 
menghadiri acara pernikahan keluarga di luar kota.
```

### **2. Mahasiswa:**
```
Kepada Yth.
Dekan Fakultas Teknik
Universitas Indonesia

Nama                    : Sari Indah
Jabatan/Posisi          : Mahasiswa S1
Instansi/Organisasi     : Universitas Indonesia

Dengan ini memohon izin pada tanggal 20 Januari 2025 
untuk mengikuti seminar nasional teknologi informasi 
yang diselenggarakan oleh IEEE Indonesia.
```

### **3. Siswa Sekolah:**
```
Kepada Yth.
Kepala Sekolah
SMA Negeri 1 Jakarta

Nama                    : Budi Santoso
Jabatan/Posisi          : Siswa Kelas XII IPA 1
Instansi/Organisasi     : SMA Negeri 1 Jakarta

Dengan ini memohon izin pada tanggal 25 Januari 2025 
untuk mengikuti olimpiade matematika tingkat provinsi.
```

## 🔧 **Fitur Cerdas**

### **1. Periode Otomatis:**
- **Satu hari**: "pada tanggal 15 Januari 2025"
- **Beberapa hari**: "dari tanggal 15 Januari 2025 sampai dengan 17 Januari 2025"
- **Tanpa periode**: hanya menyebutkan keperluan

### **2. Alamat Fleksibel:**
- Input "Ditujukan Kepada" dapat disesuaikan (Manager, Dekan, Kepala Sekolah, dll)
- Nama instansi penerima dapat berbeda dari instansi pengirim

### **3. Jabatan/Posisi:**
- Ditampilkan di data diri dan tanda tangan
- Memberikan konteks yang jelas tentang status pemohon

### **4. Placeholder Informatif:**
- Setiap field memiliki contoh yang membantu user
- Menunjukkan format yang diharapkan

## ✅ **Keunggulan Template Baru**

1. **🌐 Universal**: Dapat digunakan di berbagai konteks formal
2. **📋 Lengkap**: Field yang komprehensif untuk semua kebutuhan
3. **🎯 Kontekstual**: Menyesuaikan dengan jenis organisasi
4. **⏰ Periode Fleksibel**: Support untuk izin satu hari atau beberapa hari
5. **💼 Profesional**: Format sesuai standar surat dinas Indonesia
6. **🔄 Adaptif**: Mudah disesuaikan untuk kebutuhan spesifik

## 📊 **Backward Compatibility**

Template baru tetap kompatibel dengan penggunaan sebelumnya:
- Siswa masih bisa menggunakan dengan mengisi "Siswa" di jabatan
- Field yang tidak diisi akan menggunakan default value yang sesuai
- Format PDF tetap mengikuti standar surat resmi Indonesia

## 🚀 **Implementasi Teknis**

### **Field Definitions:**
```javascript
izin: [
  { id: "nama", label: "Nama Lengkap", type: "text" },
  { id: "jabatan", label: "Jabatan/Posisi", type: "text" },
  { id: "instansi", label: "Instansi/Organisasi", type: "text" },
  { id: "alamatPenerima", label: "Ditujukan Kepada", type: "text" },
  { id: "instansiPenerima", label: "Nama Instansi Penerima", type: "text" },
  { id: "keperluan", label: "Keperluan/Alasan Izin", type: "textarea" },
  { id: "tanggalMulai", label: "Tanggal Mulai Izin", type: "date" },
  { id: "tanggalSelesai", label: "Tanggal Selesai Izin", type: "date" },
  { id: "tanggal", label: "Tanggal Surat", type: "date" }
]
```

### **Smart Period Logic:**
```javascript
let periodeText = "";
if (values.tanggalMulai && values.tanggalSelesai) {
  if (values.tanggalMulai === values.tanggalSelesai) {
    periodeText = ` pada tanggal ${values.tanggalMulai}`;
  } else {
    periodeText = ` dari tanggal ${values.tanggalMulai} sampai dengan ${values.tanggalSelesai}`;
  }
}
```

Template Surat Izin Universal ini memberikan fleksibilitas maksimal sambil tetap mempertahankan standar format surat resmi Indonesia yang profesional dan dapat diterima di berbagai institusi.
