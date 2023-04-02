// Library untuk sensor RFID MFRC522
#include <SPI.h>
#include <MFRC522.h>

// Library untuk WiFi dan HTTP Client
#include <ESP8266WiFi.h>
#include <WiFiClient.h>
#include <ESP8266HTTPClient.h>

// WiFi Manager Library & it's dependencies
#include <DNSServer.h>
#include <ESP8266WebServer.h>
#include <WiFiManager.h>

// Libray untuk parsing JSON
#include <Arduino_JSON.h>

// Library untuk LCD I2C
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

// OTA Update
#include <ESP8266httpUpdate.h>

// Define pin untuk MFRC522
#define RST_PIN D3
#define SS_PIN D4
#define buzzer1 D8
#define switch1 3

// Create MFRC522 instance
MFRC522 mfrc522(SS_PIN, RST_PIN);

// Set the LCD address to 0x27 for a 16 chars and 2 line display
LiquidCrystal_I2C lcd(0x27, 16, 2);

// Initiate WifiManager instance
WiFiManager wifiManager;
String masterTag, updateUrl, updateMode, regMode;
bool authByTag = false;

void setup()
{
  Serial.begin(9600);
  pinMode(buzzer1, OUTPUT);
  pinMode(switch1, INPUT);

  // Inisiasi instance LCD dan menyalakan backlight
  lcd.begin();
  lcd.backlight();

  // Inisiasi koneksi ke WiFi
  wifiManager.setAPCallback(configModeCallback);
  wifiManager.autoConnect("aP-Config");

  Serial.print("\nConnected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());

  // Inisiasi instance RFID sensor
  SPI.begin();
  mfrc522.PCD_Init();
  statusReady(0);

  ESPhttpUpdate.setClientTimeout(2000);

  masterTag = getMasterUID();
  updateUrl = getUpdateUrl();
  updateMode = getUpdateMode() == "1" ? "ON" : "OFF";
  Serial.println("update url : " + updateUrl);
  Serial.println("update mode : " + updateMode);
  disableUpdate();
}

void loop()
{
  if (WiFi.status() == WL_CONNECTED)
  {
    if (getUpdateMode() == "1")
    {
      clearLCD();
      lcd.setCursor(0, 0);
      lcd.print("Firmware Update");
      firmwareUpdate();
    }
    regMode = getRegisterMode();
    if (digitalRead(switch1) == LOW || regMode == "1")
    {
      registerMode();
    }

    if (!mfrc522.PICC_IsNewCardPresent())
    {
      return;
    }

    // Select one of the cards
    if (!mfrc522.PICC_ReadCardSerial())
    {
      return;
    }

    MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);

    String uid = getUID(mfrc522.uid.uidByte, mfrc522.uid.size);
    mfrc522.PICC_HaltA(); // Halt PICC

    // Check WiFi connection status
    if (uid == masterTag)
    {
      authByTag = true;
      registerMode();
    }
    else
    {
      attendanceMode();
    }
  }
  else
  {
    notifyWifiDisconnect();
  }
  statusReady(2000);
}

String getUID(byte *buffer, byte bufferSize)
{
  String uid = "";
  for (byte i = 0; i < bufferSize; i++)
  {
    i == 0 ? uid += buffer[i] < 0x10 ? "0" : "" : uid += buffer[i] < 0x10 ? " 0" : " ";
    uid += String(buffer[i], HEX);
  }
  uid.toUpperCase();
  return uid;
}

String getMasterUID()
{
  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://canteencampuz.biz.id/api/v1/mastertag");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=";
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  String payload = http.getString();
  JSONVar response = JSON.parse(payload);
  return response["data"]["uid"];

  // Free up resources
  http.end();
}

String getUpdateUrl()
{
  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://canteencampuz.biz.id/api/v1/update/url/get");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=";
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  String payload = http.getString();
  JSONVar response = JSON.parse(payload);
  return response["data"]["update_url"];

  // Free up resources
  http.end();
}

String getUpdateMode()
{
  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://canteencampuz.biz.id/api/v1/update/mode/get");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=";
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  String payload = http.getString();
  JSONVar response = JSON.parse(payload);
  return response["data"]["update_mode"];

  // Free up resources
  http.end();
}

String getRegisterMode()
{
  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://canteencampuz.biz.id/api/v1/register/mode/get");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=";
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  String payload = http.getString();
  JSONVar response = JSON.parse(payload);
  return response["data"]["register_mode"];

  // Free up resources
  http.end();
}

void disableUpdate()
{
  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://canteencampuz.biz.id/api/v1/update/mode/disable");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=";
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);
  // Free up resources
  http.end();
}

void clearLCD()
{
  lcd.setCursor(0, 0);
  lcd.print("                ");
  lcd.setCursor(0, 1);
  lcd.print("                ");
}

void errorBuzzer()
{
  tone(buzzer1, 1000, 500);
}

void successBuzzer()
{
  tone(buzzer1, 1000, 1000);
}

void statusReady(int delayDuration)
{
  delay(delayDuration);
  clearLCD();
  lcd.setCursor(0, 0);
  lcd.print("Ready...");
  lcd.setCursor(0, 1);
  lcd.print("v1.0-rc1");
}

void attendanceMode()
{
  MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);

  String uid = getUID(mfrc522.uid.uidByte, mfrc522.uid.size);
  mfrc522.PICC_HaltA(); // Halt PICC

  WiFiClient client;
  HTTPClient http;
  // Start HTTP request definition
  http.begin(client, "http://canteencampuz.biz.id/api/v1/attendance");

  // Specify content-type header
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");
  // Data to send with HTTP POST
  String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=&uid=" + uid;
  // Send HTTP POST request
  int httpResponseCode = http.POST(httpRequestData);

  Serial.print("HTTP Response code: ");
  Serial.println(httpResponseCode);
  int responseCode = httpResponseCode;

  if (responseCode == 200 || responseCode == 201)
  {
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
  }
  else if (responseCode == 500)
  {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Server Error!");
  }
  else if (responseCode == 401)
  {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed");

    lcd.setCursor(0, 1);
    lcd.print("Unauthorized!");
  }
  else if (responseCode == 404)
  {
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
  }
  else if (responseCode == 400)
  {
    clearLCD();
    errorBuzzer();

    String payload = http.getString();
    Serial.println(payload);

    lcd.setCursor(0, 0);
    lcd.print(responseCode);
    lcd.print(" : Failed!");

    lcd.setCursor(0, 1);
    lcd.print("Bad Request!");
  }
  else if (responseCode == 406)
  {
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
  else
  {
    notifyWifiDisconnect();
  }

  // Free up resources
  http.end();
}

void registerMode()
{
  clearLCD();
  lcd.setCursor(0, 0);
  lcd.print("**Registration**");
  lcd.setCursor(0, 1);
  lcd.print("Scan New Tag...");
A:
  regMode = getRegisterMode();
  if (WiFi.status() == WL_CONNECTED)
  {
    if (digitalRead(3) == HIGH && authByTag == false && regMode == "0")
    {
      statusReady(0);
      return;
    }

    if (!mfrc522.PICC_IsNewCardPresent())
    {
      goto A;
    }

    // Select one of the cards
    if (!mfrc522.PICC_ReadCardSerial())
    {
      goto A;
    }

    MFRC522::PICC_Type piccType = mfrc522.PICC_GetType(mfrc522.uid.sak);

    String uid = getUID(mfrc522.uid.uidByte, mfrc522.uid.size);
    mfrc522.PICC_HaltA(); // Halt PICC

    if (uid != masterTag)
    {

      WiFiClient client;
      HTTPClient http;

      // Start HTTP request definition
      http.begin(client, "http://canteencampuz.biz.id/api/v1/register");

      // Specify content-type header
      http.addHeader("Content-Type", "application/x-www-form-urlencoded");
      // Data to send with HTTP POST
      String httpRequestData = "api_key=Y2FudGVlbmNhbXB1el9BUEk=&new_uid=" + uid;
      // Send HTTP POST request
      int httpResponseCode = http.POST(httpRequestData);

      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      int responseCode = httpResponseCode;

      if (responseCode == 201)
      {
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
      }
      else if (responseCode == 409)
      {
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
      }
      else if (responseCode == 500)
      {
        clearLCD();
        errorBuzzer();

        String payload = http.getString();
        Serial.println(payload);

        lcd.setCursor(0, 0);
        lcd.print(responseCode);
        lcd.print(" : Failed");

        lcd.setCursor(0, 1);
        lcd.print("Server Error!");
      }
      else if (responseCode == 401)
      {
        clearLCD();
        errorBuzzer();

        String payload = http.getString();
        Serial.println(payload);

        lcd.setCursor(0, 0);
        lcd.print(responseCode);
        lcd.print(" : Failed");

        lcd.setCursor(0, 1);
        lcd.print("Unauthorized!");
      }
      else if (responseCode == 400)
      {
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
      else
      {
        notifyWifiDisconnect();
      }

      // Free up resources
      http.end();
    }
    else
    {
      authByTag = false;
      statusReady(0);
      return;
    }
  }
  else
  {
    notifyWifiDisconnect();
  }
}

void notifyWifiDisconnect()
{
  clearLCD();
  Serial.println("WiFi Disconnected");
  lcd.setCursor(0, 0);
  lcd.print("System Offline");
  lcd.setCursor(0, 1);
  lcd.print("WiFi Disconnect");
}

void configModeCallback(WiFiManager *myWiFiManager)
{
  errorBuzzer();
  Serial.println("Entered config mode");
  Serial.println(WiFi.softAPIP());
  clearLCD();
  lcd.setCursor(0, 0);
  lcd.print("SSID : aP-Config");
  lcd.setCursor(0, 1);
  lcd.print("IP : 192.168.4.1");
  // if you used auto generated SSID, print it
  Serial.println(myWiFiManager->getConfigPortalSSID());
}

void firmwareUpdate()
{
  WiFiClientSecure client;
  client.setInsecure();

  ESPhttpUpdate.setLedPin(LED_BUILTIN, LOW);

  ESPhttpUpdate.onStart(update_started);
  ESPhttpUpdate.onEnd(update_finished);
  ESPhttpUpdate.onProgress(update_progress);
  ESPhttpUpdate.onError(update_error);
  updateUrl = getUpdateUrl();
  t_httpUpdate_return ret = ESPhttpUpdate.update(client, updateUrl);

  lcd.setCursor(0, 1);
  switch (ret)
  {
  case HTTP_UPDATE_FAILED:
    Serial.printf("HTTP_UPDATE_FAILD Error (%d): %s\n", ESPhttpUpdate.getLastError(), ESPhttpUpdate.getLastErrorString().c_str());
    lcd.print("UPDATE ERROR    ");
    break;

  case HTTP_UPDATE_NO_UPDATES:
    Serial.println("HTTP_UPDATE_NO_UPDATES");
    lcd.print("NO UPDATE       ");
    break;

  case HTTP_UPDATE_OK:
    Serial.println("HTTP_UPDATE_OK");
    lcd.print("UPDATE OK       ");
    break;
  }
}

void update_started()
{
  Serial.println("CALLBACK:  HTTP update process started");
  lcd.setCursor(0, 1);
  lcd.print("UPDATE STARTED");
}

void update_finished()
{
  Serial.println("CALLBACK:  HTTP update process finished");
  lcd.setCursor(0, 1);
  lcd.print("UPDATE FINISHED ");
}

void update_progress(int cur, int total)
{
  Serial.printf("CALLBACK:  HTTP update process at %d of %d bytes...\n", cur, total);
  float downloaded = cur;
  float totalSize = total;
  int percentage = downloaded / totalSize * 100;
  Serial.print("Downloading");
  Serial.print(percentage);
  Serial.println("%");
  lcd.setCursor(0, 1);
  lcd.print("Processing ");
  lcd.print(percentage);
  lcd.print("%   ");
}

void update_error(int err)
{
  Serial.printf("CALLBACK:  HTTP update fatal error code %d\n", err);
  lcd.setCursor(0, 1);
  lcd.print("UPDATE ERROR    ");
  delay(3000);
  ESP.reset();
}
