/*
 * ============================================================
 * FIRMWARE v2.2 - Sistem Absensi RFID (NodeMCU ESP8266)
 * ============================================================
 * Perubahan dari v1:
 *  - PERBAIKAN BUG: logika debounce tombol di v1 salah sehingga penekanan
 *    tombol tidak pernah terdeteksi.
 *  - Self-test saat boot: LED+buzzer, versi chip RC522, WiFi, ping server.
 *  - Log HTTP lengkap (URL, kode, pesan error, isi balasan).
 *  - Heartbeat tiap 5 detik (status WiFi + kondisi mentah tombol).
 *  - WiFi reconnect otomatis.
 *  - Feedback buzzer jujur: berhasil terkirim = TIT-TIT, gagal = 1 bunyi panjang.
 *
 * PIN (sesuai wiring final):
 *   RC522 SDA->D4  SCK->D5  MOSI->D7  MISO->D6  RST->D3  VCC->3V3  GND->GND
 *   LED(+, via 220R)->D8 | Buzzer(+)->D2 | Push Button->D1 (INPUT_PULLUP) & GND
 * ============================================================
 */

#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <SPI.h>
#include <MFRC522.h>

// ================== WAJIB DIISI ULANG ==================
const char* ssid          = "tata's wifi";
const char* password      = "14076090";
// Isi dengan IP laptop (XAMPP) pada jaringan/hotspot yang SAMA dengan NodeMCU
const char* serverBaseUrl = "http://10.132.104.251/absensi_rfid";
// =======================================================

#define SS_PIN     D4
#define RST_PIN    D3
#define BUZZER_PIN D2
#define LED_PIN    D8
#define BUTTON_PIN D1

MFRC522 mfrc522(SS_PIN, RST_PIN);

// --- Status tombol (debounce versi benar) ---
int buttonStable = HIGH;          // kondisi tombol yang sudah stabil
int buttonLastRead = HIGH;        // pembacaan terakhir
unsigned long buttonLastChange = 0;
const unsigned long debounceDelay = 40;

unsigned long lastHeartbeat = 0;
unsigned long lastWifiTry = 0;
bool rc522Ok = false;
byte lastVer = 0x55;          // nilai awal sentinel (supaya bacaan pertama tercetak)
unsigned long lastRcCheck = 0;

// ------------------------------------------------------------
// Pola bunyi
// ------------------------------------------------------------
void pulse(int ms) {
  digitalWrite(LED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(ms);
  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);
}

void beepOk() {            // TIT-TIT : kartu berhasil terkirim ke server
  pulse(150); delay(100);
  pulse(150);
}

void beepMode() {          // 1 bunyi sedang : mode berhasil diganti
  pulse(300);
}

void beepFail() {          // 1 bunyi panjang : gagal (WiFi/server/RC522)
  pulse(800);
}

// ------------------------------------------------------------
// WiFi
// ------------------------------------------------------------
void connectWiFi() {
  Serial.print("[WiFi] Menghubungkan ke: ");
  Serial.println(ssid);
  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  int n = 0;
  while (WiFi.status() != WL_CONNECTED && n < 40) {   // maks 20 detik
    delay(500);
    Serial.print(".");
    n++;
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("[WiFi] TERHUBUNG");
    Serial.print("[WiFi] IP NodeMCU : "); Serial.println(WiFi.localIP());
    Serial.print("[WiFi] Gateway    : "); Serial.println(WiFi.gatewayIP());
    Serial.print("[WiFi] Sinyal     : "); Serial.print(WiFi.RSSI()); Serial.println(" dBm");
  } else {
    Serial.println("[WiFi] GAGAL. Cek ssid/password & pastikan hotspot 2.4GHz.");
  }
}

void ensureWiFi() {
  if (WiFi.status() == WL_CONNECTED) return;
  if (millis() - lastWifiTry < 10000) return;
  lastWifiTry = millis();
  Serial.println("[WiFi] terputus, mencoba menyambung ulang...");
  WiFi.disconnect();
  WiFi.begin(ssid, password);
}

// ------------------------------------------------------------
// HTTP GET generik. Mengembalikan kode HTTP (>0 = ada balasan server)
// ------------------------------------------------------------
int httpGet(const String& path, String& payload) {
  payload = "";
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[HTTP] BATAL: WiFi tidak terhubung");
    return -100;
  }

  WiFiClient client;
  HTTPClient http;
  String url = String(serverBaseUrl) + path;

  Serial.print("[HTTP] GET ");
  Serial.println(url);

  http.setTimeout(4000);
  if (!http.begin(client, url)) {
    Serial.println("[HTTP] GAGAL: format URL salah");
    return -101;
  }

  int code = http.GET();
  if (code > 0) {
    payload = http.getString();
    payload.trim();
    Serial.print("[HTTP] kode=");
    Serial.print(code);
    Serial.print(" isi=\"");
    Serial.print(payload);
    Serial.println("\"");
  } else {
    Serial.print("[HTTP] GAGAL kode=");
    Serial.print(code);
    Serial.print(" (");
    Serial.print(HTTPClient::errorToString(code));
    Serial.println(")");
  }
  http.end();
  return code;
}

// ------------------------------------------------------------
// Self-test RC522: baca register versi lewat SPI
// ------------------------------------------------------------
void checkRC522() {
  byte v = mfrc522.PCD_ReadRegister(MFRC522::VersionReg);
  Serial.print("[RC522] VersionReg = 0x");
  Serial.println(v, HEX);

  if (v == 0x00 || v == 0xFF) {
    rc522Ok = false;
    Serial.println("[RC522] TIDAK TERDETEKSI -> cek kabel SDA/SCK/MOSI/MISO/RST, VCC 3.3V, GND");
  } else {
    rc522Ok = true;
    Serial.println("[RC522] OK (komunikasi SPI berhasil)");
  }
}


// Pemantau kontak RC522: dicek tiap 1 detik, selalu melaporkan kondisi sebenarnya.
// Cocok untuk "tes tekan/goyang kabel" sambil melihat Serial Monitor.
void monitorRC522() {
  if (millis() - lastRcCheck < 1000) return;
  lastRcCheck = millis();

  byte v = mfrc522.PCD_ReadRegister(MFRC522::VersionReg);
  bool valid = (v != 0x00 && v != 0xFF);

  if (v != lastVer) {
    Serial.print("[RC522] VersionReg berubah: 0x");
    Serial.print(lastVer, HEX);
    Serial.print(" -> 0x");
    Serial.println(v, HEX);
    lastVer = v;
  }

  if (valid && !rc522Ok) {
    mfrc522.PCD_Init();          // konfigurasi ulang setelah daya kembali
    delay(20);
    rc522Ok = true;
    Serial.println("[RC522] TERSAMBUNG kembali -> siap membaca kartu");
    beepOk();
  } else if (!valid && rc522Ok) {
    rc522Ok = false;
    Serial.println("[RC522] KONTAK TERPUTUS (VersionReg tidak valid) -> cek kabel/solder");
  }
}

// ------------------------------------------------------------
// Aksi tombol & kartu
// ------------------------------------------------------------
void onButtonPressed() {
  Serial.println("[BTN] DITEKAN -> minta server ganti mode");
  String body;
  int code = httpGet("/ubahmode.php", body);
  if (code == 200) {
    Serial.print("[MODE] Mode sekarang = ");
    Serial.println(body);
    beepMode();
  } else {
    beepFail();
  }
}

void handleButton() {
  int reading = digitalRead(BUTTON_PIN);

  if (reading != buttonLastRead) {
    buttonLastChange = millis();
    buttonLastRead = reading;
  }

  if ((millis() - buttonLastChange) > debounceDelay && reading != buttonStable) {
    buttonStable = reading;
    Serial.print("[BTN] kondisi stabil: ");
    Serial.println(buttonStable == LOW ? "LOW (ditekan)" : "HIGH (dilepas)");
    if (buttonStable == LOW) {
      onButtonPressed();
    }
  }
}

void handleRFID() {
  if (!rc522Ok) return;
  if (!mfrc522.PICC_IsNewCardPresent()) return;
  if (!mfrc522.PICC_ReadCardSerial()) return;

  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.print("[RFID] UID terbaca: ");
  Serial.println(uid);

  String body;
  int code = httpGet("/kirimkartu.php?nokartu=" + uid, body);
  if (code == 200 && body == "OK") {
    beepOk();
  } else {
    beepFail();
  }

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();
}

// ------------------------------------------------------------
void setup() {
  Serial.begin(115200);
  delay(300);
  Serial.println();
  Serial.println("==== Absensi RFID v2.2 (diagnostik) ====");

  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(BUTTON_PIN, INPUT_PULLUP);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_PIN, LOW);

  Serial.println("[TEST] LED + buzzer menyala 300 ms (kalau tidak, cek wiring D8/D2)");
  pulse(300);

  SPI.begin();
  mfrc522.PCD_Init();
  delay(50);
  checkRC522();
  if (!rc522Ok) beepFail();

  connectWiFi();

  if (WiFi.status() == WL_CONNECTED) {
    String body;
    int code = httpGet("/ping.php", body);
    if (code == 200) {
      Serial.println("[PING] SERVER TERJANGKAU");
    } else {
      Serial.println("[PING] SERVER TIDAK TERJANGKAU -> cek IP di serverBaseUrl, Apache, firewall");
      beepFail();
    }
  }

  buttonStable = digitalRead(BUTTON_PIN);
  buttonLastRead = buttonStable;
  Serial.print("[BTN] kondisi awal: ");
  Serial.println(buttonStable == HIGH ? "HIGH (normal, tidak ditekan)" : "LOW (TIDAK NORMAL: tombol/kabel D1 tersambung ke GND)");

  Serial.println("==== Sistem siap ====");
}

void loop() {
  ensureWiFi();
  monitorRC522();
  handleButton();
  handleRFID();

  if (millis() - lastHeartbeat >= 5000) {
    lastHeartbeat = millis();
    Serial.print("[ALIVE] WiFi=");
    Serial.print(WiFi.status() == WL_CONNECTED ? "OK" : "PUTUS");
    Serial.print(" RC522=");
    Serial.print(rc522Ok ? "OK" : "ERROR");
    Serial.print("(0x");
    Serial.print(lastVer, HEX);
    Serial.print(")");
    Serial.print(" BTN_RAW=");
    Serial.println(digitalRead(BUTTON_PIN) == HIGH ? "HIGH" : "LOW");
  }
}
