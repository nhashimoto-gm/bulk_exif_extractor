# Client-Side Bulk Metadata Extractor

## Overview

**Bulk Metadata Extractor** is a high-performance, client-side tool designed to extract comprehensive metadata from thousands of image and video files in a single batch. It operates entirely within the browser using WebAssembly and JavaScript, ensuring data privacy and speed by avoiding server-side uploads.

## Key Features

*   **Massive Batch Processing**: Optimized to handle 2000+ files efficiently without freezing the browser, utilizing asynchronous chunk processing.
*   **Folder Support**: Supports drag-and-drop of entire folders, including nested directory structures.
*   **Comprehensive Metadata Extraction**:
    *   **Images (HEIC, JPEG, PNG)**: Extracts all available metadata (including Date, GPS, Lens Info, Altitude, Speed, etc.) using `ExifReader`.
    *   **Videos (MOV, MP4)**: Extracts detailed technical metadata using `MediaInfo.js` (WebAssembly).
*   **Smart Filtering**:
    *   Automatically filters out large binary blobs to keep the output JSON lightweight.
    *   Intelligently retains readable manufacturer-specific data (e.g., Apple MakerNote) when possible.
*   **Privacy First**: All processing is local. No file data leaves the user's device.

## Technical Architecture & Development History

### 1. The Challenge: Server-Side vs. Client-Side
Processing thousands of high-resolution media files on a server is resource-intensive and slow due to upload bandwidth. This tool was developed to shift that workload to the client's browser.

### 2. Video Metadata & MediaInfo.js
Standard browser APIs cannot read detailed video metadata. We integrated **MediaInfo.js** (WebAssembly) to provide desktop-class metadata extraction for video files directly in the browser.
*   **Optimization**: To prevent initialization race conditions, robust loading logic with local asset hosting was implemented.

### 3. Solving HEIC Accuracy
Initial implementations using generic libraries often failed to extract accurate GPS coordinates from HEIC (High Efficiency Image Container) files.
*   **Solution**: The tool utilizes **ExifReader**, which provides superior parsing accuracy for HEIC files, ensuring correct GPS coordinates, altitude, and timestamps are extracted.

### 4. Performance Optimization for Large Batches
Processing 2000+ files synchronously would cause the browser tab to freeze ("Page Unresponsive").
*   **Solution**: Implemented an **Asynchronous Chunking** strategy.
    *   Files are scanned and queued in batches (e.g., 500 files).
    *   The processing loop yields control to the main UI thread periodically (`setTimeout`), keeping the interface responsive and providing real-time progress updates.

## Installation & Usage

This tool is designed to be standalone and can be deployed on any standard web server.

### Requirements
*   **Web Server**: Any static file server (Apache, Nginx, Python `http.server`, etc.). No backend logic (PHP/Python/Node.js) is required.
*   **Browser**: Modern browser (Chrome, Edge, Safari, Firefox) with WebAssembly support.

### Setup Instructions

1.  **Deploy the File**:
    *   Place the main HTML/PHP file on your web server.

2.  **Dependencies**:
    The tool relies on two key libraries. You can use CDNs or host them locally.

    *   **ExifReader** (for Images):
        *   Recommended: Use CDN or download from [ExifReader GitHub](https://github.com/mattiasw/ExifReader).
    *   **MediaInfo.js** (for Videos):
        *   Download `mediainfo.min.js` and `MediaInfoModule.wasm` from [MediaInfo.js GitHub](https://github.com/buzz/mediainfo.js).
        *   Place them in a directory (e.g., `assets/js/lib/mediainfo/`).

3.  **Configuration**:
    *   Update the `locateFile` path in the source code to point to your local `MediaInfoModule.wasm` file:
        ```javascript
        locateFile: (path, prefix) => `/path/to/your/assets/${path}`
        ```

### Usage Notes for End Users
*   **Mac/iOS Users**: When using the Apple Photos app, it is recommended to export "Unmodified Originals" to ensure all metadata (including GPS and Creation Date) is preserved before processing.
