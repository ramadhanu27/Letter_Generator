# 📄 Indonesian PDF Letter Generator

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat&logo=tailwind-css&logoColor=white)](https://tailwindcss.com/)

A modern, web-based application for generating professional Indonesian formal letters in PDF format. This tool helps users create standardized business letters, permission letters, and legal documents that comply with Indonesian formal letter writing standards.

## ✨ Features

### 📝 **Letter Templates**

- **🔄 Universal Permission Letter (Surat Izin)** - Flexible template for employees, university students, school students, and professionals
- **📄 Declaration Letter (Surat Pernyataan)** - For official statements and declarations
- **🤝 Power of Attorney (Surat Kuasa)** - For legal authorization documents

### 🎯 **Smart Features**

- **💡 Interactive Example Data** - Quick-fill buttons with realistic sample data for different professions
- **📱 Responsive Design** - Works seamlessly on desktop, tablet, and mobile devices
- **👀 Real-time Preview** - See your letter content as you type
- **✅ Smart Validation** - Automatic date validation and required field checking
- **🎨 Modern UI** - Clean, professional interface with smooth animations

### 📋 **Professional Formatting**

- **🇮🇩 Indonesian Standards** - Complies with formal Indonesian letter writing conventions
- **📏 Proper Margins** - Standard 25mm margins for official documents
- **🔤 Typography** - Times New Roman font with appropriate sizing and spacing
- **📄 PDF Generation** - High-quality PDF output with consistent formatting
- **🎯 Flexible Addressing** - Adaptable recipient addressing for different organizations

### 🔧 **Technical Features**

- **⚡ Client-side Processing** - No server required, works entirely in the browser
- **💾 Instant Download** - Generate and download PDFs immediately
- **🖼️ File Upload Support** - Add letterhead images and attachments
- **🌐 Cross-browser Compatible** - Works on all modern web browsers

## 📸 Screenshots

### Main Interface

_[Screenshot placeholder - Application main interface showing form and preview]_

### Universal Permission Letter

_[Screenshot placeholder - Permission letter template with example data]_

### Generated PDF Sample

_[Screenshot placeholder - Professional PDF output example]_

## 🚀 Installation & Setup

### Prerequisites

- Modern web browser (Chrome, Firefox, Safari, Edge)
- Local web server (optional, for development)

### Quick Start

1. **Clone the repository**

   ```bash
   git clone https://github.com/yourusername/indonesian-pdf-letter-generator.git
   cd indonesian-pdf-letter-generator
   ```

2. **Open the application**

   ```bash
   # Option 1: Direct file opening
   open index.html

   # Option 2: Using Python's built-in server
   python -m http.server 3000

   # Option 3: Using Node.js http-server
   npx http-server -p 3000
   ```

3. **Access the application**
   - Direct file: Open `index.html` in your browser
   - Local server: Navigate to `http://localhost:3000`

### No Installation Required

This application runs entirely in the browser with no backend dependencies. Simply download the files and open `index.html` in any modern web browser.

## 📖 Usage Guide

### 1. **Universal Permission Letter (Surat Izin)**

Perfect for various professional contexts:

**👔 For Employees:**

- Select "Surat Izin (Universal)" from the dropdown
- Click "👔 Karyawan" for employee example data
- Customize fields for your specific situation
- Generate PDF for submission to HR/management

**🎓 For University Students:**

- Click "🎓 Mahasiswa" for student example data
- Address to Dean, Department Head, or Academic Advisor
- Specify academic-related reasons (seminars, conferences, research)

**📚 For School Students:**

- Click "📚 Siswa" for school student example data
- Address to Principal or Class Teacher
- Include school-appropriate reasons and parental consent

### 2. **Declaration Letter (Surat Pernyataan)**

For official statements and legal declarations:

- Fill in personal information
- Specify the nature of your declaration
- Add witness information if required
- Generate legally-formatted PDF

### 3. **Power of Attorney (Surat Kuasa)**

For legal authorization documents:

- Enter grantor (pemberi kuasa) information
- Specify grantee (penerima kuasa) details
- Define scope of authorization
- Include official signatures and stamps

## 🛠️ Technical Stack

- **Frontend Framework**: Vanilla JavaScript (ES6+)
- **Styling**: Tailwind CSS 3.x
- **PDF Generation**: jsPDF library
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Inter)
- **Build Tools**: No build process required
- **Browser Support**: All modern browsers (Chrome 60+, Firefox 60+, Safari 12+, Edge 79+)

## 📁 File Structure

```
indonesian-pdf-letter-generator/
├── index.html                 # Main application file
├── script.js                  # Core JavaScript functionality
├── README.md                  # Project documentation
├── SURAT_IZIN_UNIVERSAL_UPDATE.md  # Universal permission letter documentation
├── PDF_FORMAT_IMPROVEMENTS.md      # PDF formatting standards documentation
└── assets/                    # Static assets (if any)
    └── images/               # Application screenshots
```

### Key Files Description

- **`index.html`** - Main HTML structure with Tailwind CSS styling
- **`script.js`** - Complete application logic including:
  - Form field generation and validation
  - Real-time preview functionality
  - PDF generation with Indonesian formatting standards
  - Example data management
  - Event handling and user interactions

## 🤝 Contributing

We welcome contributions to improve this Indonesian letter generator! Here's how you can help:

### Getting Started

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Test thoroughly across different browsers
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

### Contribution Guidelines

- **Code Style**: Follow existing JavaScript and HTML formatting
- **Testing**: Test on multiple browsers and devices
- **Documentation**: Update README.md for new features
- **Indonesian Standards**: Ensure compliance with formal Indonesian letter conventions
- **Accessibility**: Maintain WCAG 2.1 AA compliance where possible

### Areas for Contribution

- 🌐 Additional letter templates
- 🎨 UI/UX improvements
- 📱 Mobile responsiveness enhancements
- 🔧 Performance optimizations
- 🌍 Internationalization support
- ♿ Accessibility improvements

## 🎯 Use Cases

### **Business & Corporate**

- Employee leave requests and sick leave notifications
- Official company announcements and declarations
- Power of attorney for business transactions
- Inter-department communication letters

### **Educational Institutions**

- Student absence notifications for schools and universities
- Academic leave requests for research or conferences
- Official statements for scholarship applications
- Authorization letters for academic representatives

### **Government & Legal**

- Official declarations for legal proceedings
- Authorization documents for government services
- Formal complaints and requests to authorities
- Legal power of attorney documents

### **Personal & Family**

- Medical leave notifications for family emergencies
- Travel authorization letters for minors
- Property-related authorization documents
- Personal declaration letters for various purposes

## 🔒 Privacy & Security

- **🔐 Client-Side Processing**: All data processing happens in your browser - no information is sent to external servers
- **💾 No Data Storage**: The application doesn't store or save any personal information
- **🌐 Offline Capable**: Works without internet connection after initial load
- **🔒 Secure PDF Generation**: PDFs are generated locally using jsPDF library
- **🛡️ No Tracking**: No analytics, cookies, or user tracking implemented

## 📋 Roadmap

### **Short Term (Next Release)**

- [ ] Additional letter templates (Surat Lamaran Kerja, Surat Resign)
- [ ] Enhanced mobile responsiveness
- [ ] Improved PDF formatting options
- [ ] Template validation improvements

### **Medium Term**

- [ ] Multi-language support (English templates)
- [ ] Advanced PDF customization (fonts, colors, layouts)
- [ ] Template saving and loading functionality
- [ ] Print-friendly versions

### **Long Term**

- [ ] Batch letter generation
- [ ] Integration with cloud storage services
- [ ] API for third-party integrations
- [ ] Advanced template editor

## ❓ FAQ & Troubleshooting

### **Common Questions**

**Q: Can I use this application offline?**
A: Yes! After the initial page load, the application works completely offline. All processing happens in your browser.

**Q: Are my documents and data secure?**
A: Absolutely. No data is sent to any server - everything is processed locally in your browser. We don't store, track, or have access to any information you enter.

**Q: Can I customize the letter templates?**
A: Currently, the templates follow Indonesian formal letter standards. You can modify the content through the form fields, but template structure customization is planned for future releases.

**Q: What file formats are supported for attachments?**
A: The application supports common image formats (JPG, PNG, GIF) up to 5MB for letterhead and attachment purposes.

**Q: Can I save my work and continue later?**
A: Currently, the application doesn't save data between sessions. Template saving functionality is planned for future releases. We recommend keeping your information in a separate document for reuse.

### **Troubleshooting**

**Issue: PDF generation fails or produces blank pages**

- Solution: Ensure all required fields are filled out completely
- Check that your browser supports modern JavaScript features
- Try refreshing the page and re-entering your information

**Issue: Application doesn't load properly**

- Solution: Make sure you're using a modern browser (Chrome 60+, Firefox 60+, Safari 12+, Edge 79+)
- Disable browser extensions that might interfere with JavaScript
- Clear your browser cache and reload the page

**Issue: Generated PDF formatting looks incorrect**

- Solution: This usually indicates a browser compatibility issue
- Try using a different browser (Chrome recommended for best results)
- Ensure your browser's zoom level is set to 100%

**Issue: Example data buttons don't work**

- Solution: Make sure you've selected "Surat Izin (Universal)" from the dropdown first
- The example buttons only appear for the Universal Permission Letter template
- Try refreshing the page if buttons remain unresponsive

## 🐛 Bug Reports & Feature Requests

Please use the [GitHub Issues](https://github.com/yourusername/indonesian-pdf-letter-generator/issues) page to:

- Report bugs with detailed reproduction steps
- Request new features or letter templates
- Suggest improvements to existing functionality
- Ask questions about usage or implementation

### **When Reporting Bugs**

Please include:

- Browser name and version
- Operating system
- Steps to reproduce the issue
- Expected vs actual behavior
- Screenshots if applicable

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🇮🇩 Indonesian Letter Standards

This application strictly follows Indonesian formal letter writing conventions:

### **Format Standards**

- **Margins**: 25mm on all sides for official documents
- **Font**: Times New Roman, 12pt for body text
- **Spacing**: 1.5x line spacing for readability
- **Alignment**: Justified text with proper indentation
- **Date Format**: Indonesian format (DD Month YYYY)

### **Structure Standards**

- **Letterhead**: Optional company/institution header
- **Date and Place**: Right-aligned, Indonesian format
- **Recipient Address**: Left-aligned, formal addressing
- **Salutation**: "Dengan hormat," (With respect)
- **Body**: Structured paragraphs with clear purpose
- **Closing**: "Hormat saya," (Respectfully yours)
- **Signature**: Name and title/position

### **Language Standards**

- **Formal Indonesian**: Proper Bahasa Indonesia grammar
- **Respectful Tone**: Appropriate level of formality
- **Standard Phrases**: Commonly accepted formal expressions
- **Professional Terminology**: Industry-appropriate vocabulary

## 📚 Additional Resources

### **Indonesian Letter Writing Guides**

- [Pedoman Umum Ejaan Bahasa Indonesia (PUEBI)](https://puebi.js.org/)
- [Surat Menyurat Resmi - Kemendikbud](https://www.kemdikbud.go.id/)
- [Panduan Surat Resmi Pemerintah](https://www.setneg.go.id/)

### **Related Tools & Libraries**

- [jsPDF Documentation](https://github.com/parallax/jsPDF)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Indonesian Date Formatting](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat)

## 🙏 Acknowledgments

- **Indonesian Government** - For formal letter writing standards and guidelines
- **Tailwind CSS** - For the excellent utility-first CSS framework
- **jsPDF** - For client-side PDF generation capabilities
- **Font Awesome** - For beautiful icons and visual elements
- **Community Contributors** - For feedback, bug reports, and feature suggestions
- **Indonesian Language Community** - For maintaining formal writing standards

## 🌟 Support

If you find this project helpful, please consider:

- ⭐ Starring the repository
- 🐛 Reporting bugs and issues
- 💡 Suggesting new features
- 🤝 Contributing code improvements
- 📢 Sharing with others who might benefit

---

**Made with ❤️ for the Indonesian community**

_This application is designed specifically for Indonesian formal letter standards and conventions. All generated documents comply with official Indonesian business letter formatting requirements._
