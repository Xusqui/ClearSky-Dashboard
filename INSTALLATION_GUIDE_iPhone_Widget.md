---
# iPhone Weather Widget Installation & Usage Guide
## Complete Setup for Scriptable Widget

---

## 📦 What's Included

Your weather station now has a complete iPhone widget system with:

### Scripts (in `/scripts/`)
- **iPhoneWeatherWidget.js** - Main widget script (recommended)
- **iPhoneWeatherWidget_Alternativas.js** - Alternative versions (compact, detailed, astronomy, indoor/outdoor)
- **README_iPhone_Widget.md** - Complete installation guide
- **QUICK_REFERENCE.md** - Quick reference for developers

### API Endpoint (in `/static/config/`)
- **iphone_widget_api.php** - JSON API endpoint for the widget
- **test_iphone_widget_api.php** - Testing tool to verify API functionality

---

## 🚀 Quick Start (5 minutes)

### Step 1: Download Scriptable
- Open App Store on your iPhone
- Search for **"Scriptable"**
- Install (it's free)

### Step 2: Create the Widget Script
1. Open Scriptable
2. Tap **"+"** to create a new script
3. Copy the entire content from `iPhoneWeatherWidget.js`
4. Paste into the script editor
5. **Important**: Change line 5 to your server URL:
   ```javascript
   const API_URL = 'https://your-domain.com/static/config/iphone_widget_api.php';
   ```
6. Save with name: **"Estación Meteorológica"**

### Step 3: Add Widget to Home Screen
1. Go to your iPhone home screen
2. Long press empty area → "Add Widget"
3. Search for **"Scriptable"** → Select it
4. Choose **"Medium"** size (recommended)
5. Tap "Add Widget"
6. Long press the new widget → "Edit Widget"
7. In **"Select a script"**, choose **"Estación Meteorológica"**
8. Done! The widget should display weather data

---

## 📊 Widget Data Display

The widget shows:

| Data | Icon | Unit |
|------|------|------|
| Temperature | 🌡️ | °C |
| Heat Index | 🤔 | °C |
| Dew Point | ❄️ | °C |
| Humidity | 💧 | % |
| Wind Speed | 💨 | km/h |
| Wind Gust | 🌪️ | km/h |
| Pressure | ⬇️ | mb |
| Daily Rain | 🌧️ | mm |
| Weekly Rain | 📊 | mm |
| Monthly Rain | 📅 | mm |
| Yearly Rain | 📈 | mm |
| UV Index | ☀️ | - |
| Solar Radiation | ☀️ | W/m² |
| Indoor Temp | 🏠 | °C |
| Indoor Humidity | 🏠 | % |

---

## 🎨 Color Themes

The widget automatically adapts to your iPhone's theme:
- **Light Mode**: Light background, dark text
- **Dark Mode**: Dark background, light text

Temperature colors change dynamically:
- 🔵 Blue: Below 0°C (freezing)
- 🟢 Green: 0-10°C (cold)
- 🟡 Yellow: 10-20°C (cool)
- 🟠 Orange: 20-30°C (warm)
- 🔴 Red: Above 30°C (hot)

Rain color indicator:
- 🟡 Yellow: No rain
- 🔵 Light blue: Light rain (< 1 mm)
- 🔵 Medium blue: Moderate rain (1-5 mm)
- 🔵 Dark blue: Heavy rain (> 5 mm)

---

## 🔄 Update Frequency

The widget updates every **5 minutes** by default.

To change the update interval in the script:
```javascript
const REFRESH_MINUTES = 5;  // Change this number
```

---

## 📱 Alternative Widget Versions

The file `iPhoneWeatherWidget_Alternativas.js` contains 4 additional versions:

### 1. Compact Version (`versionCompacta()`)
- Minimal information
- Perfect for small screen space
- Shows: Temperature, humidity, wind, rain

### 2. Detailed Version (`versionDetallada()`)
- Complete information
- Organized in sections
- Requires scrolling on small widgets

### 3. Astronomy Version (`versionAstronomica()`)
- Focused on solar and UV data
- Solar radiation levels
- UV risk assessment

### 4. Indoor/Outdoor Version (`versionInteriorExterior()`)
- Compares interior vs exterior
- Side-by-side data comparison
- Shows VPD (Vapor Pressure Deficit)

### How to Use Alternative Versions

1. Copy the function from `iPhoneWeatherWidget_Alternativas.js`
2. Add it to your main script
3. Replace the widget creation line:
   ```javascript
   // Change this:
   const widget = await createWeatherWidget(data, colors);
   
   // To one of:
   const widget = await versionCompacta();
   const widget = await versionDetallada();
   const widget = await versionAstronomica();
   const widget = await versionInteriorExterior();
   ```

---

## ⚙️ Configuration

### Change API URL
Find line 5 in `iPhoneWeatherWidget.js`:
```javascript
const API_URL = 'https://your-domain.com/static/config/iphone_widget_api.php';
```

### Customize Colors

Find the color definitions:
```javascript
// Light theme colors
const COLORS = {
    bg: new Color('#f5f5f7'),        // Background
    text: new Color('#000000'),      // Main text
    secondary: new Color('#666666'), // Secondary text
    accent: new Color('#007AFF'),    // Accent color
    warning: new Color('#FF9500'),   // Warning color
    danger: new Color('#FF3B30'),    // Danger color
    success: new Color('#34C759'),   // Success color
};
```

### Widget Size
Currently optimized for **Medium** size. The script can be adapted for:
- `small` - Compact display
- `medium` - Balanced (recommended)
- `large` - Full information

---

## 🔍 Testing

### Test the API Directly

Open your browser and visit:
```
https://your-domain.com/static/config/test_iphone_widget_api.php
```

This page will show:
- ✅ Configuration file status
- ✅ Database connection status
- ✅ Weather data in real-time
- ✅ JSON output for the widget
- 📊 Statistics about your data

### Test Script Manually

1. Open the script in Scriptable
2. Press **"▶"** (Play button)
3. The widget should appear
4. Check the console for any errors

---

## 🐛 Troubleshooting

### Widget Shows "Error obteniendo datos"
**Solutions:**
1. Check if the API URL is correct and accessible
2. Verify internet connection on your iPhone
3. Test the API using `test_iphone_widget_api.php`
4. Check server logs for errors

### Widget Doesn't Update
**Solutions:**
1. Run the script manually in Scriptable
2. Check refresh interval settings
3. Restart Scriptable app
4. Restart your iPhone
5. Check if sensors are sending data to the database

### Data Seems Outdated
**Solutions:**
1. Verify the weather station sensors are working
2. Check that the database is receiving new data
3. Confirm the `timestamp` field is being updated
4. Check the server timezone settings

### API Returns Error in Browser
**Solutions:**
1. Verify database credentials in `config_db.php`
2. Check if the `meteo` table exists
3. Ensure you have query permissions
4. Check server error logs

---

## 🔐 Security Considerations

### Current Configuration
The API allows requests from any origin:
```php
header('Access-Control-Allow-Origin: *');
```

### For Production
Consider restricting to your domain:
```php
header('Access-Control-Allow-Origin: https://your-domain.com');
```

### Best Practices
1. Use HTTPS (SSL certificate) for data security
2. Monitor API usage and logs
3. Consider adding rate limiting
4. Keep database credentials secure
5. Regular backups of your database

---

## 📈 Next Steps & Improvements

### Planned Features
- [ ] Lock Screen widgets (iOS 16+)
- [ ] Rain notifications
- [ ] 24-hour forecast
- [ ] Historical data charts
- [ ] Data sharing with other apps
- [ ] Multi-language support
- [ ] Custom widget backgrounds

### How to Contribute
1. Test with different iOS versions
2. Report any issues or bugs
3. Suggest new data fields to display
4. Propose design improvements

---

## 📚 File Structure

```
/Volumes/web/weather/
├── scripts/
│   ├── iPhoneWeatherWidget.js              (Main widget script)
│   ├── iPhoneWeatherWidget_Alternativas.js (Alternative versions)
│   ├── README_iPhone_Widget.md             (Installation guide)
│   ├── QUICK_REFERENCE.md                  (Developer reference)
│   └── metricsWidget.js                    (Existing metrics widget)
└── static/
    └── config/
        ├── iphone_widget_api.php           (API endpoint)
        ├── test_iphone_widget_api.php      (API testing tool)
        ├── config_db.php                   (Database credentials)
        └── [other config files]
```

---

## 🎯 Support Resources

### For Users
- Complete guide: `README_iPhone_Widget.md` in `/scripts/` folder
- Scriptable documentation: https://scriptable.app/
- Scriptable community: https://talk.scriptable.app/

### For Developers
- Quick reference: `QUICK_REFERENCE.md` in `/scripts/` folder
- API documentation: Available in `iphone_widget_api.php`
- Testing tool: `test_iphone_widget_api.php`

---

## 📝 Version Information

- **Widget Version**: 1.0
- **API Version**: 1.0
- **Created**: January 28, 2026
- **iOS Requirement**: 14.0+
- **Required App**: Scriptable (Free)

---

## ✅ Installation Checklist

Before using the widget, verify:

- [ ] Scriptable app installed on iPhone
- [ ] API URL updated in the script
- [ ] Database is accessible and populated
- [ ] Database table `meteo` exists
- [ ] At least one weather reading exists in database
- [ ] Internet connection available on iPhone
- [ ] Widget added to home screen
- [ ] Script selected in widget settings
- [ ] Weather data is displaying correctly

---

## 🎉 You're All Set!

Your iPhone weather widget is ready to use. The widget will automatically update every 5 minutes with the latest data from your weather station.

Enjoy real-time weather data on your iPhone home screen! ☀️

---

**Need Help?** Check the `README_iPhone_Widget.md` file for detailed instructions.

**Want to Contribute?** See `QUICK_REFERENCE.md` for development guidelines.
