#ifndef ___Varible_Config___
#define ___Varible_Config___

#include <Arduino.h>
#include <EEPROM.h>
#include <Wifi_Setup.h>
#include <NTP_Time_Setup.h>
#include <GPS_Neo_Setup.h>
#include <update_Data.h>
#include <SHT30.h>
#include <HSTS016L_Lib.h>
#include <EEPROM_Write_Time.h>

#define I2C_SDA 21
#define I2C_SCL 22

#define CURRENT_PIN 36
#define CALIB_PIN 39

#define NUM_SAMPLES 4000
#define SAMPLE_INTERVAL 200
#define CURRENT_OFFSET 0.5

#define OPR_CURRENT_THRESHOLD 10

#define OPR_VOLTAGE 14.7

extern HSTS016L_Config_t sensorConfig;
extern SHT30 _sensor;

extern uint32_t interval;
extern uint32_t lastMillis;

extern uint32_t interval_2;
extern uint32_t lastMillis_2;

extern float temperature1;
extern float humidity1;

extern float temperature2;
extern float humidity2;

extern float temperature3;
extern float humidity3;

extern double latitude;
extern double longitude;

extern float nowCurrent;
extern float nowPower;

extern String currentTime;

extern void beginSerialCommunication();

extern void selectChannel(uint8_t bus);

extern void SHT30_Init();

extern void HSTS016L_Init();

extern void read_And_Check_Current();

extern void read_Temp_And_Humi();

extern void read_Lat_And_Long();

extern void read_Real_Time();

extern void print_Data_Var();

extern void update_Data_To_Server();

extern void read_Machine_Power();

#endif // ___Varible_Config___
