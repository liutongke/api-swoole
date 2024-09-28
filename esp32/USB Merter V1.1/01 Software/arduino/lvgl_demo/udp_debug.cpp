#include <WiFi.h>
#include <WiFiUdp.h> //引用以使用UDP


const char *ssid = "OliverLuo";
const char *password = "imagin888";
char udp_send_buff[256];

WiFiUDP Udp;                      //创建UDP对象
unsigned int localUdpPort = 2333; //本地端口号


void wifi_init(){

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);
  while (!WiFi.isConnected())
  {
    delay(500);
    Serial.print(".");
  }
  Serial.println("Connected");
  Serial.print("IP Address:");
  Serial.println(WiFi.localIP());

  Udp.begin(localUdpPort); //启用UDP监听以接收数据
}

void udp_debug(char *str){
   if(!WiFi.isConnected())
      return;
   IPAddress local_IP(192, 168, 3, 166);
   Udp.beginPacket(local_IP, 1234);
   //Udp.print("Received: ");
   Udp.write((const uint8_t*)str, strlen(str)); //复制数据到发送缓存
   Udp.endPacket();
}
