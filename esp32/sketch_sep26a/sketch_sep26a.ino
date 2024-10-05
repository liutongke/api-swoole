void setup() {  
  // 初始化串行通信，波特率设置为9600  
  Serial.begin(9600);  
}  
  
void loop() {  
  // 通过串行端口发送"hello world"  
  Serial.println("hello world");  
    
  // 为了避免连续不断地发送消息，我们可以添加一个小的延时  
  delay(1000); // 延时1秒（1000毫秒）  
}
