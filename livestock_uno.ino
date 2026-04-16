#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// --- KINGA BROWNOUT DETECTOR ---
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"

// --- OLED LIBRARIES ---
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// --- WIFI & SERVER ---
const char* ssid       = "muhura121";
const char* password   = "134567890";
const char* computerIP = "192.168.137.236";

// --- OLED SETUP ---
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
#define OLED_RESET    -1
#define SCREEN_ADDRESS 0x3C

Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);

// --- RFID PINS ---
#define SS_PIN   5
#define RST_PIN  4
MFRC522 mfrc522(SS_PIN, RST_PIN);

// --- OTHER PINS ---
#define LED_GREEN   12
#define LED_RED     13
#define BUZZER      27
#define BUTTON_PIN  14

// --- NETWORK ---
IPAddress local_IP(192,168,137,50);
IPAddress gateway(192,168,137,1);
IPAddress subnet(255,255,255,0);

// --- VARIABLES ---
bool regMode = false;
unsigned long lastScanTime = 0;
const long scanDelay = 4000;
unsigned long lastButtonPress = 0;
bool lastButtonState = HIGH;
unsigned long displayResetTime = 0;
bool showingResult = false;

// --- SETUP ---
void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0); // Kinga Brownout
  
  Serial.begin(115200);
  
  // OLED INIT
  Wire.begin(21, 22);
  if(!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
    Serial.println(F("OLED Failed!"));
    for(;;);
  }
  
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  display.setCursor(0,0);
  display.println("LiveTrack Pro");
  display.println("Booting...");
  display.display();
  
  // RFID
  SPI.begin();
  mfrc522.PCD_Init();
  
  // PINS
  pinMode(LED_GREEN, OUTPUT);
  pinMode(LED_RED, OUTPUT);
  pinMode(BUZZER, OUTPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  
  digitalWrite(BUZZER, LOW);
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, LOW);
  
  // WIFI
  WiFi.mode(WIFI_STA);
  WiFi.config(local_IP, gateway, subnet);
  WiFi.begin(ssid, password);
  
  int timeout = 0;
  while (WiFi.status() != WL_CONNECTED && timeout < 20) {
    delay(500);
    Serial.print(".");
    timeout++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    display.clearDisplay();
    display.setCursor(0,0);
    display.println("WiFi Connected");
    display.println(WiFi.localIP().toString());
    display.display();
    beep(2, 100);
    delay(2000);
    resetDisplay();
  } else {
    display.clearDisplay();
    display.setCursor(0,0);
    display.println("WiFi Failed");
    display.println("Check Hotspot");
    display.display();
    delay(3000);
  }
}

// --- LOOP ---
void loop() {
  unsigned long currentMillis = millis();
  
  // ========== BUTTON: Single Click Toggle ==========
  bool currentButtonState = digitalRead(BUTTON_PIN);
  
  if (lastButtonState == HIGH && currentButtonState == LOW) {
    lastButtonPress = currentMillis;
  } 
  else if (lastButtonState == LOW && currentButtonState == HIGH) {
    if (currentMillis - lastButtonPress > 50) {
      regMode = !regMode;
      beep(regMode ? 3 : 1, 150);
      
      display.clearDisplay();
      display.setCursor(0,0);
      display.setTextSize(2);
      display.println(regMode ? "REGISTER" : "SCAN");
      display.setTextSize(1);
      display.println(regMode ? "Mode Active" : "Mode Active");
      display.display();
      
      displayResetTime = currentMillis;
      showingResult = true;
    }
  }
  lastButtonState = currentButtonState;

  // ========== RFID SCAN ==========
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    if (currentMillis - lastScanTime > scanDelay) {
      String tagID = "";
      for (byte i = 0; i < mfrc522.uid.size; i++) {
        tagID += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
        tagID += String(mfrc522.uid.uidByte[i], HEX);
      }
      tagID.toUpperCase();
      Serial.println("TAG: " + tagID);
      
      processTag(tagID);
      lastScanTime = currentMillis;
      displayResetTime = currentMillis;
      showingResult = true;
    }
    
    mfrc522.PICC_HaltA();
    mfrc522.PCD_StopCrypto1();
  }
  
  // ========== AUTO RESET DISPLAY ==========
  if (showingResult && (currentMillis - displayResetTime > 5000)) {
    resetDisplay();
    showingResult = false;
  }
}

// --- PROCESS TAG ---
void processTag(String id) {
  display.clearDisplay();
  display.setCursor(0,0);
  display.setTextSize(1);
  display.println("ID: " + id);
  display.println("Checking...");
  display.display();
  
  if (WiFi.status() != WL_CONNECTED) {
    errorFeedback("WiFi Offline");
    return;
  }
  
  HTTPClient http;
  int httpCode = 0;
  
  // 🔥 Banza ushakishe Tag muri Database (Hatitawe kuri Mode)
  String scanUrl = "http://" + String(computerIP) + "/livestock/livestocks/animal.php?tagId=" + id;
  http.begin(scanUrl);
  httpCode = http.GET();
  
  if (httpCode == 200) {
    String payload = http.getString();
    StaticJsonDocument<1024> doc;
    DeserializationError error = deserializeJson(doc, payload);
    
    if (!error && doc["success"]) {
      // ✅ TAG IRABONEKA MURI DATABASE
      
      String name = doc["name"].as<String>();
      int sick = doc["isSick"] | 0;
      int pregnant = doc["isPregnant"] | 0;
      String birthdate = doc["birthdate"].as<String>();
      String ownerName = doc["ownerName"].as<String>();
      String animalType = doc["animalType"].as<String>();
      String sex = doc["sex"].as<String>();
      
      String ageStr = calculateAge(birthdate);
      
      digitalWrite(LED_GREEN, HIGH);
      tone(BUZZER, 2400, 150);
      delay(150);
      noTone(BUZZER);
      digitalWrite(BUZZER, LOW);
      
      displayAnimalDetails(name, sick, pregnant, ageStr, ownerName, animalType, sex);
      
      if (regMode) {
        // Niba uri muri REGISTER MODE, yerekana ko isanzwe irimo
        display.setCursor(0, 56);
        display.setTextSize(1);
        display.print("[Already Registered]");
        display.display();
      }
      
      http.end();
      return;
    }
  }
  
  // ❌ TAG NTIRABONEKA MURI DATABASE
  http.end();
  
  if (regMode) {
    // REGISTER MODE: Yandike Tag nshya
    String url = "http://" + String(computerIP) + "/livestock/livestocks/register.php";
    http.begin(url);
    http.addHeader("Content-Type", "application/json");
    String jsonBody = "{\"tagId\":\"" + id + "\"}";
    httpCode = http.POST(jsonBody);
    
    Serial.println("Register URL: " + url);
    Serial.println("HTTP Code: " + String(httpCode));
    
    if (httpCode == 200) {
      String payload = http.getString();
      StaticJsonDocument<512> doc;
      DeserializationError error = deserializeJson(doc, payload);
      
      if (!error) {
        if (doc["success"]) {
          successFeedback("Registered!");
        } else {
          const char* msg = doc["message"] | "Failed";
          errorFeedback(String(msg));
        }
      } else {
        errorFeedback("JSON Error");
      }
    } else {
      errorFeedback("HTTP: " + String(httpCode));
    }
  } else {
    // SCAN MODE: Tag ntiriho
    errorFeedback("Unknown Tag");
  }
  
  http.end();
}

// --- KUBARA IMYAKA (AGE) ---
String calculateAge(String birthdate) {
  if (birthdate.length() < 10) return "?";
  
  int bYear = birthdate.substring(0, 4).toInt();
  int currentYear = 2026;
  int age = currentYear - bYear;
  
  if (age < 0) return "0 yrs";
  if (age == 0) return "<1 yr";
  return String(age) + " yrs";
}

// --- KWEREKANA AMAKURU Y'INYAMASWA (OLED) ---
void displayAnimalDetails(String name, int sick, int pregnant, String age, String owner, String type, String sex) {
  display.clearDisplay();
  display.setTextSize(1);
  display.setTextColor(SSD1306_WHITE);
  
  // Line 1: Izina + Status
  display.setCursor(0, 0);
  display.print(name.substring(0, 10));
  display.print(" ");
  display.print(sick == 1 ? "[SICK]" : "[OK]");
  
  // Line 2: Inda + Age
  display.setCursor(0, 16);
  if (sex == "Female" && pregnant == 1) {
    display.print("Pregnant | ");
  } else if (sex == "Female") {
    display.print("Open | ");
  } else {
    display.print("Male | ");
  }
  display.print(age);
  
  // Line 3: Ubwoko
  display.setCursor(0, 32);
  display.print(type.substring(0, 14));
  
  // Line 4: Nyiri yo
  display.setCursor(0, 48);
  display.print("By: ");
  display.print(owner.substring(0, 12));
  
  display.display();
  
  displayResetTime = millis();
  showingResult = true;
}

// --- SUCCESS FEEDBACK ---
void successFeedback(String msg) {
  digitalWrite(LED_GREEN, HIGH);
  digitalWrite(LED_RED, LOW);
  
  tone(BUZZER, 2400, 150);
  delay(150);
  noTone(BUZZER);
  digitalWrite(BUZZER, LOW);
  
  display.clearDisplay();
  display.setCursor(0, 20);
  display.setTextSize(2);
  display.println("SUCCESS");
  display.setTextSize(1);
  display.setCursor(0, 45);
  display.println(msg);
  display.display();
  
  displayResetTime = millis();
  showingResult = true;
}

// --- ERROR FEEDBACK ---
void errorFeedback(String msg) {
  digitalWrite(LED_RED, HIGH);
  digitalWrite(LED_GREEN, LOW);
  
  tone(BUZZER, 600, 300);
  delay(300);
  noTone(BUZZER);
  digitalWrite(BUZZER, LOW);
  
  display.clearDisplay();
  display.setCursor(0, 20);
  display.setTextSize(2);
  display.println("ERROR");
  display.setTextSize(1);
  display.setCursor(0, 45);
  display.println(msg);
  display.display();
  
  displayResetTime = millis();
  showingResult = true;
}

// --- RESET DISPLAY ---
void resetDisplay() {
  digitalWrite(LED_GREEN, LOW);
  digitalWrite(LED_RED, LOW);
  
  display.clearDisplay();
  display.setCursor(0, 0);
  display.setTextSize(2);
  display.println(regMode ? "REG MODE" : "LIVETRACK");
  display.setTextSize(1);
  display.setCursor(0, 40);
  display.println("Ready...");
  display.display();
}

// --- BEEP FUNCTION ---
void beep(int times, int duration) {
  for (int i = 0; i < times; i++) {
    tone(BUZZER, 2000, duration);
    delay(duration + 30);
    noTone(BUZZER);
    digitalWrite(BUZZER, LOW);
    delay(20);
  }
}