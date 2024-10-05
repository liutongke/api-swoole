#include <TFT_eSPI.h>  // 引入TFT_eSPI库
#include <SPI.h>      // 引入SPI库

TFT_eSPI tft = TFT_eSPI();  // 创建TFT_eSPI对象

void setup(void) {
  tft.init();                // 初始化显示屏
  tft.setRotation(0);       // 设置显示方向
}

void loop() {
  // 清空屏幕
  tft.fillScreen(TFT_BLACK);
  
  // 获取屏幕宽度和高度
  int screenWidth = tft.width();
  int screenHeight = tft.height();

  // 绘制四个区域
  tft.fillRect(0, 0, screenWidth / 2, screenHeight / 2, TFT_RED);     // 左上区域
  tft.fillRect(screenWidth / 2, 0, screenWidth / 2, screenHeight / 2, TFT_GREEN); // 右上区域
  tft.fillRect(0, screenHeight / 2, screenWidth / 2, screenHeight / 2, TFT_BLUE);  // 左下区域
  tft.fillRect(screenWidth / 2, screenHeight / 2, screenWidth / 2, screenHeight / 2, TFT_YELLOW); // 右下区域

  // 在每个区域中添加文本
  tft.setTextColor(TFT_WHITE);  // 设置文本颜色为白色
  tft.setTextSize(2);            // 设置文本大小

  // 左上区域
  tft.setCursor(10, 10);         // 设置光标位置
  tft.println("Upper left area");

  // 右上区域
  tft.setCursor(screenWidth / 2 + 10, 10); // 设置光标位置
  tft.println("Upper right area");

  // 左下区域
  tft.setCursor(10, screenHeight / 2 + 10); // 设置光标位置
  tft.println("Lower left area");

  // 右下区域
  tft.setCursor(screenWidth / 2 + 10, screenHeight / 2 + 10); // 设置光标位置
  tft.println("Lower right area");
  
  delay(5000);  // 显示5秒
}
