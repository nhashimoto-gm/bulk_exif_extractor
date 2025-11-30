# Bulk EXIF/Metadata Extractor

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![Version](https://img.shields.io/badge/version-1.0-green.svg)](https://github.com/nhashimoto-gm/bulk_exif_extractor)
[![Language](https://img.shields.io/badge/language-HTML%2FJavaScript-orange.svg)](https://github.com/nhashimoto-gm/bulk_exif_extractor)
[![Browser](https://img.shields.io/badge/browser-Chrome%20%7C%20Edge%20%7C%20Safari%20%7C%20Firefox-brightgreen.svg)](https://github.com/nhashimoto-gm/bulk_exif_extractor)

## Overview

**Bulk EXIF/Metadata Extractor** is a high-performance, client-side web application designed to extract comprehensive metadata from thousands of image and video files in a single batch operation. It operates entirely within the browser using WebAssembly and JavaScript, ensuring **complete data privacy** by avoiding server-side uploads.

Perfect for photographers, videographers, archivists, and anyone who needs to extract metadata from large media collections efficiently.

## ✨ Key Features

### 🚀 Massive Batch Processing
- Optimized to handle **2000+ files** efficiently without freezing the browser
- Utilizes asynchronous chunk processing to keep the UI responsive
- Real-time progress tracking with speed metrics and time estimates

### 📁 Folder Support
- Drag-and-drop entire folders with nested directory structures
- Preserves relative file paths in the output
- Supports both folder selection and individual file selection

### 📊 Comprehensive Metadata Extraction

**Images (HEIC, JPEG, PNG):**
- All available EXIF data using [ExifReader](https://github.com/mattiasw/ExifReader)
- GPS coordinates (Latitude, Longitude, Altitude)
- Date/Time information
- Camera settings (ISO, Aperture, Shutter Speed)
- Lens information
- And much more...

**Videos (MOV, MP4):**
- Detailed technical metadata using [MediaInfo.js](https://github.com/buzz/mediainfo.js) (WebAssembly)
- Video codec information
- Audio track details
- Duration, bitrate, frame rate
- Resolution and aspect ratio

### 🎯 Smart Filtering
- Automatically filters out large binary blobs to keep output JSON lightweight
- Intelligently retains readable manufacturer-specific data (e.g., Apple MakerNote) when possible
- GPS coordinates automatically converted to decimal format for easy mapping

### 🔒 Privacy First
- **100% client-side processing** - no file data leaves your device
- No server uploads required
- All processing happens in your browser

## 🛠️ Technical Architecture

### Why Client-Side?
Processing thousands of high-resolution media files on a server is:
- **Resource-intensive** and costly
- **Slow** due to upload bandwidth limitations
- **Privacy-concerning** due to sensitive media data

This tool solves these issues by shifting the workload entirely to the client's browser.

### Performance Optimizations

1. **Asynchronous Chunking Strategy**
   - Files are processed in small batches (5 files at a time)
   - Processing loop yields control to the UI thread periodically
   - Prevents "Page Unresponsive" errors on large batches

2. **Efficient Library Loading**
   - Robust initialization with proper error handling
   - MediaInfo.js WebAssembly module loaded locally for reliability
   - ExifReader CDN integration for image processing

3. **Smart Filtering**
   - Removes binary blobs and oversized data
   - Retains only useful, readable metadata
   - Keeps output JSON compact and manageable

### Technology Stack

- **ExifReader v4.20.0** - Superior HEIC/JPEG/PNG metadata parsing
- **MediaInfo.js** - WebAssembly-powered video metadata extraction
- **Bootstrap 5.3** - Modern, responsive UI
- **Vanilla JavaScript** - No framework dependencies

## 📦 Installation & Setup

### Requirements

- **Web Server**: Any static file server (Apache, Nginx, Python `http.server`, etc.)
  - No backend logic required (PHP/Python/Node.js not needed for execution)
- **Browser**: Modern browser with WebAssembly support
  - Chrome/Edge 57+
  - Firefox 52+
  - Safari 11+

### Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/nhashimoto-gm/bulk_exif_extractor.git
   cd bulk_exif_extractor
   ```

2. **Download MediaInfo.js Dependencies**

   Download the following files from [MediaInfo.js GitHub](https://github.com/buzz/mediainfo.js):
   - `mediainfo.min.js`
   - `MediaInfoModule.wasm`

   Place them in: `assets/js/lib/mediainfo/`

3. **Configure the Path**

   Update the `locateFile` path in `bulk_exif_extractor.php` (line 188):
   ```javascript
   locateFile: (path, prefix) => `/assets/js/lib/mediainfo/${path}`
   ```

   Adjust this path to match your server directory structure.

4. **Deploy to Web Server**

   Place the files on your web server and access via browser.

   **Quick Test (Python):**
   ```bash
   python3 -m http.server 8000
   ```
   Then open `http://localhost:8000/bulk_exif_extractor.php`

## 🎯 Usage

### Basic Workflow

1. **Open the Application** in your web browser
2. Wait for the initialization message ("MediaInfo initialized")
3. **Choose your input method:**
   - Drag & drop files or folders
   - Click "Select Folder" button
   - Click "Select Files" button
4. **Monitor Progress** with real-time updates
5. **Download Results** as JSON when complete

### Controls

- **Pause** - Temporarily halt processing (UI remains responsive)
- **Resume** - Continue from where you left off
- **Stop** - Cancel processing and download results so far
- **Reset** - Clear everything and start over

### Output Format

Results are exported as a JSON file with the following structure:

```json
{
  "path/to/image1.jpg": {
    "Make": "Apple",
    "Model": "iPhone 13 Pro",
    "DateTime": "2024:01:15 14:30:22",
    "GPSLatitudeDecimal": 35.6762,
    "GPSLongitudeDecimal": 139.6503,
    "GPSAltitude": 42.5,
    ...
  },
  "path/to/video1.mov": {
    "media": {
      "@ref": "path/to/video1.mov",
      "track": [...]
    }
  }
}
```

### 📸 Special Notes for Mac/iOS Users

When using the Apple Photos app:
- The Photos Library cannot be directly selected
- **Recommended workflow:**
  1. Open Photos app
  2. Select files/albums you want to process
  3. Go to **File > Export > Export Unmodified Original**
  4. Use the exported folder with this tool

This ensures all metadata (GPS, Creation Date, etc.) is preserved.

## 🔧 Development Notes

### HEIC Accuracy Challenge

Many generic EXIF libraries fail to extract accurate GPS coordinates from HEIC files. This tool uses **ExifReader**, which provides superior parsing accuracy for:
- HEIC (High Efficiency Image Container)
- Accurate GPS coordinates
- Altitude data
- Timestamp information

### MediaInfo.js Integration

Standard browser APIs cannot read detailed video metadata. By integrating **MediaInfo.js** (WebAssembly), this tool provides:
- Desktop-class metadata extraction
- Support for MOV/MP4 formats
- Detailed codec and technical information

### Browser Compatibility

The application has been tested on:
- ✅ Chrome 90+
- ✅ Edge 90+
- ✅ Safari 14+
- ✅ Firefox 88+

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 Changelog

### v1.0 (2025-01-30)
- Initial release
- Support for HEIC, JPEG, PNG images
- Support for MOV, MP4 videos
- Batch processing up to 2000+ files
- Real-time progress tracking
- Privacy-focused client-side processing

## 🙏 Acknowledgments

- [ExifReader](https://github.com/mattiasw/ExifReader) - Excellent EXIF parsing library
- [MediaInfo.js](https://github.com/buzz/mediainfo.js) - WebAssembly video metadata extraction
- [Bootstrap](https://getbootstrap.com/) - UI framework

## 📧 Support

For issues, questions, or suggestions, please [open an issue](https://github.com/nhashimoto-gm/bulk_exif_extractor/issues) on GitHub.

---

**Made with ❤️ for photographers and videographers who value their privacy**
