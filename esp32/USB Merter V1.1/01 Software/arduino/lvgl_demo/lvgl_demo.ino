#include <lvgl.h>
#include <TFT_eSPI.h>
#include <lv_examples/lv_examples.h>
#include <Wire.h>
#include <Bounce2.h>
#include <INA.h>
#include <SPI.h>
#include <Ticker.h>
#include "udp_debug.h"


#define LCD_BL      26

TFT_eSPI tft = TFT_eSPI(); /* TFT instance */
static lv_disp_buf_t disp_buf;
static lv_color_t buf[LV_HOR_RES_MAX * 10];



INA_Class         INA; 



int8_t request_index=0;

volatile uint8_t  deviceNumber    = UINT8_MAX;  ///< Device Number to use in example
volatile uint64_t sumBusMillVolts = 0;          ///< Sum of bus voltage readings
volatile int64_t  sumBusMicroAmps = 0;          ///< Sum of bus amperage readings
volatile uint8_t  readings        = 0;          ///< Number of measurements taken
volatile int64_t  mAH = 0;

bool power_on = 0;

lv_obj_t * label1;
lv_obj_t * label2;
lv_obj_t * label3;
lv_obj_t * label4;

lv_obj_t * label_v;
lv_obj_t * label_a;
lv_obj_t * label_w;
lv_obj_t * label_mah;

Ticker ticker1;


void ina226_read() {
  /*!
    @brief Interrupt service routine for the INA pin
    @details Routine is called whenever the INA_ALERT_PIN changes value
  */
 
  sumBusMillVolts += INA.getBusMilliVolts(deviceNumber);  // Add current value to sum
  sumBusMicroAmps += INA.getBusMicroAmps(deviceNumber);   // Add current value to sum
  readings++;

}  // of ISR for handling interrupts

void ina266_task()
{
  char tmp[64];
  static long lastMillis = millis();  // Store the last time we printed something
  volatile uint64_t vol,cur, wat;
  double v,a,w;
  
  if((millis() - lastMillis) >= 500 )
  {
    vol = INA.getBusMilliVolts(deviceNumber);
    cur = INA.getBusMicroAmps(deviceNumber)/1000;
    wat = INA.getBusMicroWatts(deviceNumber);
    lastMillis = millis();

    if(cur > 20*1000)
      cur = 0;

    v = vol / 1000.0;
    if(v>10)
      sprintf(tmp,"#00FFFF %0.2f",v);
    else
      sprintf(tmp,"#00FFFF %0.3f",v);
    lv_label_set_text(label1,tmp);

    a = cur/1000.0;
    if(a > 10)
      sprintf(tmp,"#0000FF %0.2f",a);
    else
      sprintf(tmp,"#0000FF %0.3f",a);
    lv_label_set_text(label2,tmp);

    w = v*a;
    if(w>10)
      sprintf(tmp,"#FF0000 %0.2f",w);
    else 
      sprintf(tmp,"#FF0000 %0.3f",w); 
    lv_label_set_text(label3,tmp);

    mAH += cur;
    sprintf(tmp,"#00FF00 %0.3f", mAH/(60*60)/1000.0);
    lv_label_set_text(label4,tmp);

    sprintf(tmp,"vol:%0.3fV cur:%dmA, power:%0.3fW %dmah\n", v, a, w,mAH/(60*60)/1000.0);
    Serial.print(tmp);
  }  
}



#if LV_USE_LOG != 0
/* Serial debugging */
void my_print(lv_log_level_t level, const char* file, uint32_t line, const char* fun, const char* dsc)
{
	Serial.printf("%s@%d %s->%s\r\n", file, line, fun, dsc);
	Serial.flush();
}
#endif

/* Display flushing */
void my_disp_flush(lv_disp_drv_t* disp, const lv_area_t* area, lv_color_t* color_p)
{
	uint32_t w = (area->x2 - area->x1 + 1);
	uint32_t h = (area->y2 - area->y1 + 1);

	tft.startWrite();
	tft.setAddrWindow(area->x1, area->y1, w, h);
	tft.pushColors(&color_p->full, w * h, true);
	tft.endWrite();

	lv_disp_flush_ready(disp);
}

static void list_event_handler(lv_obj_t * obj, lv_event_t event)
{
  if (event == LV_EVENT_CLICKED) {
    printf("Clicked: %s\n", lv_list_get_btn_text(obj));
  }
}

void setup()
{
	Serial.begin(115200); /* prepare for possible serial debug */

  pinMode(LCD_BL, OUTPUT);
  digitalWrite(LCD_BL,HIGH);  

	lv_init();

#if LV_USE_LOG != 0
	lv_log_register_print_cb(my_print); /* register print function for debugging */
#endif

	tft.begin(); /* TFT init */
	tft.setRotation(3); /* mirror */

	lv_disp_buf_init(&disp_buf, buf, NULL, LV_HOR_RES_MAX * 10);

	/*Initialize the display*/
	lv_disp_drv_t disp_drv;
	lv_disp_drv_init(&disp_drv);
	disp_drv.hor_res = 160;
	disp_drv.ver_res = 80;
	disp_drv.flush_cb = my_disp_flush;
	disp_drv.buffer = &disp_buf;
	lv_disp_drv_register(&disp_drv);

  lv_obj_t* bgk;
  bgk = lv_obj_create(lv_scr_act(), NULL);//创建对象
  lv_obj_clean_style_list(bgk, LV_OBJ_PART_MAIN); //清空对象风格
  lv_obj_set_style_local_bg_opa(bgk, LV_OBJ_PART_MAIN, LV_STATE_DEFAULT, LV_OPA_100);//设置颜色覆盖度100%，数值越低，颜色越透。
  lv_obj_set_style_local_bg_color(bgk, LV_OBJ_PART_MAIN, LV_STATE_DEFAULT, LV_COLOR_BLACK);//设置背景颜色为绿色
  lv_obj_set_size(bgk, 160, 80);//设置覆盖大小  

  // 字体
  static lv_style_t font_style;
  lv_style_init(&font_style);
  lv_style_set_text_font(&font_style, LV_STATE_DEFAULT, &lv_font_montserrat_20);

  // 电压
  label1 = lv_label_create(lv_scr_act(), NULL);
  lv_obj_add_style(label1,LV_LABEL_PART_MAIN, &font_style);
  lv_label_set_long_mode(label1, LV_LABEL_LONG_SROLL_CIRC);     /*Break the long lines*/
  lv_label_set_recolor(label1, true);                      /*Enable re-coloring by commands in the text*/
  //lv_label_set_align(label1, LV_LABEL_ALIGN_CENTER);       /*Center aligned lines*/
  lv_obj_set_width(label1, 120);
  //lv_obj_align(label1, NULL, LV_ALIGN_IN_TOP_LEFT, 0, 2);
  lv_label_set_text(label1,"0.000");
  lv_obj_set_pos(label1, 40, 0);

  // 电压单位V
  label_v = lv_label_create(lv_scr_act(), NULL);
  lv_label_set_recolor(label_v, true); 
  lv_label_set_text(label_v,"#00FFFF V");
  lv_obj_set_pos(label_v, 102, 4);  
  
  // 电流
  label2 = lv_label_create(lv_scr_act(), NULL);
  lv_obj_add_style(label2,LV_LABEL_PART_MAIN, &font_style);
  lv_label_set_long_mode(label2, LV_LABEL_LONG_SROLL_CIRC);     /*Break the long lines*/
  lv_label_set_recolor(label2, true);                      /*Enable re-coloring by commands in the text*/
  //lv_label_set_align(label2, LV_LABEL_ALIGN_CENTER);       /*Center aligned lines*/
  lv_obj_set_width(label2, 80);
  lv_label_set_text(label2,"0.000");
  //lv_obj_align(label2, label1, LV_ALIGN_OUT_RIGHT_MID, 0, 2); 
  lv_obj_set_pos(label2, 40, 20); 

  // 电流单位A
  label_a = lv_label_create(lv_scr_act(), NULL);
  lv_label_set_recolor(label_a, true); 
  lv_label_set_text(label_a,"#0000FF A");
  lv_obj_set_pos(label_a, 102, 24);

  // 功率
  label3 = lv_label_create(lv_scr_act(), NULL);
  lv_obj_add_style(label3,LV_LABEL_PART_MAIN, &font_style);
  lv_label_set_long_mode(label3, LV_LABEL_LONG_SROLL_CIRC);     /*Break the long lines*/
  lv_label_set_recolor(label3, true);                      /*Enable re-coloring by commands in the text*/
  //lv_label_set_align(label3, LV_LABEL_ALIGN_CENTER);       /*Center aligned lines*/
  lv_obj_set_width(label3, 160);
  lv_label_set_text(label3,"0.000");
  lv_obj_set_pos(label3, 40, 40);
  //lv_obj_align(label3, label2, LV_ALIGN_OUT_BOTTOM_MID, 0, 2); 

  // 功率单位W
  label_w = lv_label_create(lv_scr_act(), NULL);
  lv_label_set_recolor(label_w, true);
  lv_label_set_text(label_w,"#FF0000 W");
  lv_obj_set_pos(label_w, 100, 45);

  // 容量
  label4 = lv_label_create(lv_scr_act(), NULL);
  lv_obj_add_style(label4,LV_LABEL_PART_MAIN, &font_style);
  lv_label_set_long_mode(label4, LV_LABEL_LONG_SROLL_CIRC);     /*Break the long lines*/
  lv_label_set_recolor(label4, true);                      /*Enable re-coloring by commands in the text*/
  //lv_label_set_align(label4, LV_LABEL_ALIGN_CENTER);       /*Center aligned lines*/
  lv_obj_set_width(label4, 160);
  lv_label_set_text(label4,"0.000");
  lv_obj_set_pos(label4, 40, 60);
  //lv_obj_align(label4, label3, LV_ALIGN_OUT_BOTTOM_MID, 0, 2);  

  // 容量mah
  label_mah = lv_label_create(lv_scr_act(), NULL);
  lv_label_set_recolor(label_mah, true);
  lv_label_set_text(label_mah,"#00FF00 Ah");
  lv_obj_set_pos(label_mah, 100, 64);  

  digitalWrite(LCD_BL,LOW);   
  
  Wire.begin();
  uint8_t devicesFound = 0;
  //lv_label_set_text(label1,"INA226 Init...");
  while (deviceNumber == UINT8_MAX)  // Loop until we find the first device
  {
    devicesFound = INA.begin(10, 10000);  // +/- 1 Amps maximum for 0.01 Ohm resistor
    Serial.println(INA.getDeviceName(devicesFound - 1));
    for (uint8_t i = 0; i < devicesFound; i++) {
      /* Change the "INA226" in the following statement to whatever device you have attached and
         want to measure */
      if (strcmp(INA.getDeviceName(i), "INA226") == 0) {
        deviceNumber = i;
        INA.reset(deviceNumber);  // Reset device to default settings
        //lv_label_set_text(label1,"INA226 Init OK");
        break;
      }  // of if-then we have found an INA226
    }    // of for-next loop through all devices found
    if (deviceNumber == UINT8_MAX) {
      Serial.print(F("No INA found. Waiting 5s and retrying...\n"));
      //lv_label_set_text(label1,"No INA found. Waiting 5s and retrying...");
      delay(5000);
    }  // of if-then no INA226 found
  }    // of if-then no device found
  Serial.print(F("Found INA at device number "));
  Serial.println(deviceNumber);
  Serial.println();
  INA.setAveraging(4, deviceNumber); 
  INA.setBusConversion(8244, deviceNumber);             // Maximum conversion time 8.244ms
  INA.setShuntConversion(8244, deviceNumber);           // Maximum conversion time 8.244ms
  INA.setMode(INA_MODE_CONTINUOUS_BOTH, deviceNumber);  // Bus/shunt measured continuously

  ticker1.attach(1, ina266_task);
}



void loop()
{
	lv_task_handler(); /* let the GUI do its work */

  //ina266_task();
  
	delay(5);
}
