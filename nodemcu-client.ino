// Library untuk sensor RFID MFRC522
#include <SPI.h>
#include <MFRC522.h>

// Library untuk WiFi dan HTTP Client
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>

// Libray untuk parsing JSON
#include <Arduino_JSON.h>

// Library untuk LCD I2C
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// Define pin untuk MFRC522
#define RST_PIN D3
#define SS_PIN D4

// Create MFRC522 instance
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Set the LCD address to 0x27 for a 16 chars and 2 line display
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Define Wifi Credential
const char* ssid = "PrivateAccess";
const char* password = "2ndfloor";

String masterTag;

void setup() {
  Serial.begin(9600);

  // Setup PinMode untuk Buzzer
  pinMode(D8, OUTPUT);

  // Setup PinMode untuk Mode Switch
  pinMode(3, INPUT);

  // Inisiasi instance LCD dan menyalakan backlight
  lcd.begin();
  lcd.backlight();

  // Inisiasi koneksi ke WiFi
  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());

  // Inisiasi instance RFID sensor
  SPI.begin();
  mfrc522.PCD_Init();
  statusReady();
  masterTag = getMasterUID();
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  // Select one of the cards
  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);

  String uid = getUID(mfrc522.uid.uidByte, mfrc522.uid.size);
  mfrc522.PICC_HaltA();  // Halt PICC

  //Check WiFi connection status
  if (WiFi.status() == WL_CONNECTED) {
    if (uid == masterTag) {
      clearLCD(); 
      lcd.setCursor(0,0);
      lcd.print("**Registration**");
      lcd.setCursor(0,1);
      lcd.print("Scan New Tag...");
      registerMode();
    } else {
      attendanceMode();
    }
  } else {
    notifyWifiDisconnect();
  }
  statusReady();
}


String getUID(byte* buffer, byte bufferSize) {
  String uid = "";
  for (byte i = 0; i < bufferSize; i++) {
    i == 0 ? uid += buffer[i] < 0x10 ? "0" : "" : uid += buffer[i] < 0x10 ? " 0" : " ";
    uid += String(buffer[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

String getMasterUID(){
  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://hfdzam.akuonline.my.id/api/v1/mastertag");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=hapiskocak";
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

String payload = http.getString();
JSONVar response = JSON.parse(payload);
return response["data"]["uid"];

  // Free up resources
  http.end();

}

void clearLCD() {
  lcd.setCursor(0, 0);
  lcd.print("                ");
  lcd.setCursor(0, 1);
  lcd.print("                ");
}

void errorBuzzer() {
  digitalWrite(D8, HIGH);
  delay(500);
  digitalWrite(D8, LOW);
}

void successBuzzer() {
  digitalWrite(D8, HIGH);
  delay(1000);
  digitalWrite(D8, LOW);
}

void statusReady() {
  delay(3000);
  clearLCD();
  lcd.setCursor(0, 0);
  lcd.print("Ready...");
}



void attendanceMode() {
  MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);

  String uid = getUID(mfrc522.uid.uidByte, mfrc522.uid.size);
  mfrc522.PICC_HaltA();  // Halt PICC

  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://hfdzam.akuonline.my.id/api/v1/attendance");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=hapiskocak&uid=" + uid;
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  Serial.print("HTTP Response code: ");
  Serial.println(httpResponseCode);
  int responseCode = httpResponseCode;

  if (responseCode == 200 || responseCode == 201) {
    clearLCD();
    successBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Success!");

    JSONVar response = JSON.parse(payload);
    lcd.setCursor(0, 1);
    lcd.print((String)response["data"]["employee_name"]);

  } else if (responseCode == 500) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Server Error!");
  } else if (responseCode == 401) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Unauthorized!");
  } else if (responseCode == 404) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);
    JSONVar response = JSON.parse(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Not Found!");

    lcd.setCursor(0, 1);
    lcd.print((String)response["data"]["uid"]);
  } else if (responseCode == 400) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed!");

    lcd.setCursor(0, 1);
    lcd.print("Bad Request!");
  } else if (responseCode == 406) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Hm, Who Are You?");
  }

  // Free up resources
  http.end();
}


void registerMode() {
  A:
  if (!mfrc522.PICC_IsNewCardPresent()) {
    goto A;
  }

  // Select one of the cards
  if (!mfrc522.PICC_ReadCardSerial()) {
    goto A;
  }

  MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);

  String uid = getUID(mfrc522.uid.uidByte, mfrc522.uid.size);
  mfrc522.PICC_HaltA();  // Halt PICC

  if (uid != masterTag){
    
  WiFiClient client;
  HTTPClient http;

  // Start HTTP request definition
  http.begin(client, "http://hfdzam.akuonline.my.id/api/v1/register");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=hapiskocak&new_uid=" + uid;
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  Serial.print("HTTP Response code: ");
  Serial.println(httpResponseCode);
  int responseCode = httpResponseCode;

  if (responseCode == 201) {
    clearLCD();
    successBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Registered");

    JSONVar response = JSON.parse(payload);
    lcd.setCursor(0, 1);
    lcd.print((String)response["data"]["new_uid"]);

  } else if (responseCode == 409) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Conflict");

    JSONVar response = JSON.parse(payload);
    lcd.setCursor(0, 1);
    lcd.print((String)response["data"]["conflict_uid"]);

  } else if (responseCode == 500) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Server Error!");
  } else if (responseCode == 401) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Unauthorized!");
  } else if (responseCode == 400) {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Bad Request!");
  }

  // Free up resources
  http.end();
  }else{
    statusReady();
    return;
  }

}

void notifyWifiDisconnect() {
  Serial.println("WiFi Disconnected");
  clearLCD();
  lcd.setCursor(7, 0);
  lcd.print("Wifi");
  lcd.setCursor(3, 1);
  lcd.print("Disconnected");
}