# ImagenFlow

**ImagenFlow** is a high-performance, AI-powered native image generation plugin for the WordPress Gutenberg editor. It bridges the gap between your written content and professional-grade visual assets using the latest Google Gemini API.

## 🌟 Key Features

- **Imagen 4.0 Integration**: Text-to-Image generation using the state-of-the-art `imagen-4.0-generate-001` model.
- **Gemini 3 Flash Power**: High-speed content summarization and smart vision-based Alt Text generation.
- **Native Block Transformation**: Automatically replaces the generation block with a core WordPress Image block for seamless management.
- **Fine-Tuned SEO**:
    - **Custom Naming Keywords**: Add your brand/keyword to filenames automatically.
    - **Smart Alt Text**: Concise, keyword-rich Alt Text generated specifically for each image.
    - **WebP Conversion**: Automatic conversion and EXIF metadata stripping for maximum performance.

---

## 💻 Technical Specifications

> [!IMPORTANT]
> This is a professional-grade technical plugin. Please review the requirements carefully before installation.

### Server Requirements
- **Hosting**: VPS or Dedicated Server is highly recommended. Shared hosting must explicitly support Node.js/React build processes and high-memory PHP operations.
- **PHP Version**: 8.1 or higher.
- **PHP Extensions**: **Imagick** (ImageMagick) is **MANDATORY** for metadata stripping and high-precision processing.
- **WordPress Version**: 6.2 or higher.

### API Requirements
- Requires a **Google AI Studio API Key**.
- Users are responsible for their own API usage costs. Check [Google AI Studio Pricing](https://ai.google.dev/pricing) for details (Free tier usually available).

---

## � Installation & Setup

### Option 1: Standard Zip Upload (Production)
1. Download the compiled ZIP of the plugin.
2. Go to **Plugins > Add New > Upload Plugin** in your WordPress Dashboard.
3. Install and **Activate**.
4. Navigate to **Settings > ImagenFlow** to enter your API Key.

### Option 2: Git Clone (Development)
If you are installing from source, you **must** build the block assets:
1. Clone the repository into `/wp-content/plugins/imagen-flow`.
2. Open your terminal in the plugin directory.
3. Run `npm install` to install dependencies.
4. Run `npm run build` to compile the Gutenberg blocks.
5. Activate the plugin in WordPress.

---

## 💰 Google AI Studio API Setup
1. Visit [Google AI Studio](https://aistudio.google.com/).
2. Login with your Google account.
3. Click on **"Get API key"** in the sidebar.
4. Select **"Create API key in new project"**.
5. Copy the Key and paste it into the **ImagenFlow Settings** in WordPress.

---

## ⚠️ Disclaimer
Users are expected to have basic technical knowledge of WordPress management and API configurations. **The Beacon Team** is not responsible for errors arising from inadequate server specifications (missing Imagick, incompatible PHP versions, or lack of React/JS build support).

---

## 💬 Support & Feedback

Have you found a bug or have an idea for improvement?
- **Discord**: Join the discussion at [The Beacon Discord Channel](https://discord.com/channels/1451777935328677931/1461584338985418762).
- **GitHub**: Feel free to open an issue in the GitHub repository.

---

## 🤝 Credits
Developed with ❤️ by **The Beacon Team**.
Visit us at: [https://beacon.co.id](https://beacon.co.id)
