#include <Wire.h>
#include "udp_debug.h"

#define FUSB302_I2C_SLAVE_ADDR 0x22


typedef struct
{
  uint32_t PDC_INF;
} PD_Source_Capabilities_TypeDef;

uint8_t CCx_PIN_Useful = 0;
uint8_t RX_Length = 0;
uint8_t PD_STEP = 0;

uint8_t RX_Token, RX_len;
uint16_t RX_Header;
uint32_t RX_Data[8];// max 8 len

uint8_t USB302_TX_Buff[20];

uint8_t PD_Source_Capabilities_Inf_num = 0;
PD_Source_Capabilities_TypeDef PD_Source_Capabilities_Inf[8];

uint8_t PD_MSG_ID = 0;
uint8_t PD_Version;

uint8_t PPS_State = 0; //pps的控制状态 0：不是pps档 1：在pps档非调整模式 2：pps档调整模式

bool USB302_INT = 0;




String TOKEN_PARSE(uint8_t token) {

  switch ((token >> 5) & 0x7) {
    case 7:
      return (String)"SOP";
    case 6:
      return (String)"SOP1";
    case 5:
      return (String)"SOP2";
    case 4:
      return (String)"SOP1DB";
    case 3:
      return (String)"SOP2DB";
    default:
      return (String)"ERROR";
  }
}

String HEADER_PARSE(uint16_t header) {

  String ret = "";

  if (header & 0x8000)
    ret += "Extended,";

  uint8_t num = (header >> 12) & 0x7;
  if (num)
    ret += (String)"DataMsg Len=" + num + ",";
  else
    ret += "CtrlMsg,";
    
  num = (header >> 9) & 0x7;
  ret += (String)"MsgID=" + num + ",";

  return ret;

}

const uint8_t PD_Resq[14] =
{
  0x12, 0x12, 0x12, 0x13, 0x86, //Token & Length
  0x42, 0x14,                   //Header
  0x00, 0x00, 0x00, 0x03,       //Object
  0xff, 0x14, 0xA1              //Token
};

void PD_Msg_ID_ADD(void)
{
  PD_MSG_ID++;
  if (PD_MSG_ID > 7)PD_MSG_ID = 0;
}

void USB302_Wite_Reg(uint8_t reg, uint8_t val) {
  Wire.beginTransmission(FUSB302_I2C_SLAVE_ADDR);
  Wire.write(reg & 0xFF);
  Wire.write(val & 0xFF);
  Wire.endTransmission();

}

void USB302_Wite_FIFO(uint8_t* buf, uint8_t len) {
  Wire.beginTransmission(FUSB302_I2C_SLAVE_ADDR);
  Wire.write(0x43);
  for (int i = 0; i < len; i++)
    Wire.write(buf[i]);
  Wire.endTransmission();

}

uint8_t USB302_Read_Reg(uint8_t reg) {
  Wire.beginTransmission(FUSB302_I2C_SLAVE_ADDR);
  Wire.write(reg & 0xFF);
  Wire.endTransmission(false);
  Wire.requestFrom(FUSB302_I2C_SLAVE_ADDR, 1, true);

  while (Wire.available())   // slave may send less than requested
  {
    return Wire.read();    // receive a byte as character
  }

}

void USB302_Read_FIFO(uint8_t *data, uint8_t length)
{
  Wire.beginTransmission(FUSB302_I2C_SLAVE_ADDR);
  Wire.write(0x43);
  Wire.endTransmission(false);
  Wire.requestFrom(FUSB302_I2C_SLAVE_ADDR, length, true);
  int cnt = 0;
  while (Wire.available())   // slave may send less than requested
  {
    data[cnt++] = Wire.read();    // receive a byte as character
  }
}

//检测cc脚上是否有连接
//返回 0 失败， 1 成功
uint8_t USB302_Check_CCx(void)
{
  uint8_t Read_State;
  USB302_Wite_Reg(0x0C, 0x02); // PD Reset
  USB302_Wite_Reg(0x0C, 0x03); // Reset FUSB302
  delay(5);
  USB302_Wite_Reg(0x0B, 0x0F); // FULL POWER!
  USB302_Wite_Reg(0x02, 0x07); // Switch on MEAS_CC1
  delay(2);
  Read_State = USB302_Read_Reg(0x40); //读状态
  USB302_Wite_Reg(0x02, 0x03);//切换到初始状态
  Read_State &= 0x03; //只看低2位 看主机有没有电压
  if (Read_State > 0)
  {
    CCx_PIN_Useful = 1;
    return 1;
  }
  USB302_Wite_Reg(0x02, 0x0B); // Switch on MEAS_CC2
  delay(2);
  Read_State = USB302_Read_Reg(0x40); //读状态
  USB302_Wite_Reg(0x02, 0x03);//切换到初始状态
  Read_State &= 0x03; //只看低2位 看主机有没有电压
  if (Read_State > 0)
  {
    CCx_PIN_Useful = 2;
    return 1;
  }
  return 0;

}

uint8_t USB302_Init(void)
{
  if (USB302_Check_CCx() == 0)
  {
    udp_debug("check CC failed\n");
    return 0; //检查有没有接着设备
  }
  udp_debug("detect CC!\n");
  USB302_Wite_Reg(0x09, 0x40);//发送硬件复位包
  USB302_Wite_Reg(0x0C, 0x03); // Reset FUSB302
  //USB302_EXTI_Init();
  delay(5);
  USB302_Wite_Reg(0x09, 0x07);//使能自动重试 3次自动重试
  USB302_Wite_Reg(0x0E, 0xFC);//使能各种中断
  //USB302_Wite_Reg(0x0F, 0xFF);
  USB302_Wite_Reg(0x0F, 0x01);
  USB302_Wite_Reg(0x0A, 0xEF);
  USB302_Wite_Reg(0x06, 0x00);//清空各种状态
  USB302_Wite_Reg(0x0C, 0x02);//复位PD
  if (CCx_PIN_Useful == 1)
  {
    //USB302_Wite_Reg(0x02, 0x07); // Switch on MEAS_CC1
    USB302_Wite_Reg(0x02, 0x05); // Switch on MEAS_CC1
    USB302_Wite_Reg(0x03, 0x41); // Enable BMC Tx on_CC1 PD3.0
    //USB302_Wite_Reg(0x03, 0x45); // Enable BMC Tx on_CC1 PD3.0 AutoCRC
  }
  else if (CCx_PIN_Useful == 2)
  {
    //USB302_Wite_Reg(0x02, 0x0B); // Switch on MEAS_CC2
    USB302_Wite_Reg(0x02, 0x0A); // Switch on MEAS_CC2
    USB302_Wite_Reg(0x03, 0x42); // Enable BMC Tx on CC2 PD3.0
    //USB302_Wite_Reg(0x03, 0x46); // Enable BMC Tx on_CC1 PD3.0 AutoCRC
  }
  USB302_Wite_Reg(0x0B, 0x0F);//全电源

  USB302_Wite_Reg(0x07, 0x03);//全电源

  USB302_Read_Reg(0x3E);
  USB302_Read_Reg(0x3F);
  USB302_Read_Reg(0x42);
  RX_Length = 0;
  USB302_INT = 0;
  PD_STEP = 0;
  PD_Source_Capabilities_Inf_num = 0;
  /*  USB302_Wite_Reg(0x07, 0x04); // Flush RX*/
  udp_debug("fusb302 init finish!\n");
  return 1;
}

void USB302_Read_Service(void)//读取服务
{
  USB302_Read_Reg(0x3E);
  USB302_Read_Reg(0x42); //清中断

  RX_Token = USB302_Read_Reg(0x43) & 0xe0;

  if (RX_Token > 0x40) //E0 C0 A0 80 60 都是允许的值
  { //小端 高8位后来
    //Serial.println();
    //Serial.print("Token:");
    //Serial.println(TOKEN_PARSE(RX_Token));

    USB302_Read_FIFO((uint8_t*)&RX_Header, 2);

    //Serial.print("Header:");
    //Serial.println(HEADER_PARSE(RX_Header));

    RX_len = (((RX_Header) >> 12) & 7); //Control Msg bytes len

    USB302_Read_FIFO((uint8_t*)RX_Data, RX_len * 4);

    //for (int i = 0; i < RX_len; i++)
     // Serial.println(RX_Data[i], BIN);
  } else {
    RX_Token = 0;
  }
  USB302_Wite_Reg(0x07, 0x04);//清空RX FIFO
}

void USB302_Data_Service(void)//数据服务
{
  char tmp[32];
  
  {
    USB302_Read_Service();

    if (RX_Token) //token有效
    {
      PD_Msg_ID_ADD();
      if (RX_len == 0) //控制消息
      {
        udp_debug("Control Message:");
        switch (RX_Header & 0xF)//获取包类型
        {
          case 1://GoodCRC
            udp_debug("GoodCRC\n"); //GoodCRC
            break;
          case 3://Accept
            udp_debug("Accept\n");//Accept
            break;
          case 4://Reject
            udp_debug("Reject\n");//Reject
            break;
          case 6://PS_RDY
            udp_debug("PS_RDY\n");//PS_RDY
            break;
          //          case 8://Get_Sink_Cap  必须回复点东西
          //            delay(1);
          //            break;
          default:
            sprintf(tmp, "%bb", RX_Header & 0xF);
            udp_debug(tmp);
            //Serial.println(RX_Header & 0xF, BIN);//PS_RDY
            break;
        }
      }
      else//数据消息
      {
        udp_debug("Data Message:\n");
        sprintf(tmp, "header:%x\n", RX_Header & 0xF);
        udp_debug(tmp);        
        if ((RX_Header & 0xF) == 0x01) //Source_Capabilities
        {
          udp_debug("Source_Capabilities:");
          if (PD_STEP == 0)
          {
            uint8_t reg = RX_Header & 0xC0;
            PD_Version = 0x80;//reg;
            reg >>= 1;
            if (CCx_PIN_Useful == 1) //调整PD版本
            {
              reg |= 0x05;
            }
            else if (CCx_PIN_Useful == 2)
            {
              reg |= 0x06;
            }
            USB302_Wite_Reg(0x03, reg);
            delayMicroseconds(200);
            USB302_Wite_Reg(0x0C, 0x02); // Reset PD
            USB302_Wite_Reg(0x07, 0x04);
            PD_STEP = 1;
            USB302_INT = 0;
            PD_MSG_ID = 0; //现在开始正式从0开始记录
            //udp_debug("ID Reset.");
            return;
          }

          PD_Source_Capabilities_Inf_num = RX_len;
          //Serial.print(PD_Source_Capabilities_Inf_num);
          sprintf(tmp, "%d", PD_Source_Capabilities_Inf_num);
          udp_debug(tmp);          
          udp_debug("Modes.");

          for (int i = 0; i < PD_Source_Capabilities_Inf_num; i++)
            PD_Source_Capabilities_Inf[i].PDC_INF = RX_Data[i];

          PD_STEP = 2;
          PPS_State = 0; //恢复pps 挡位
        } else {
          //Serial.println(RX_Header & 0xF, BIN);//PS_RDY
          sprintf(tmp, "header:%x", RX_Header & 0xF);
          udp_debug(tmp);
        }

      }

    }
  }
}

void USB302_Send_Requse(uint8_t objects)
{
  uint8_t i;
  uint16_t cachecur;
  if (objects > PD_Source_Capabilities_Inf_num) 
    return;

  //Load_Requse_TX_Buff();
  for (i = 0; i < 14; i++) //装填发送buff
  {
    USB302_TX_Buff[i] = PD_Resq[i];
  }

  USB302_TX_Buff[6] |= PD_MSG_ID << 1;
  USB302_TX_Buff[5] |= PD_Version;

  USB302_TX_Buff[10] |= (objects + 1) << 4;
  uint8_t type = (PD_Source_Capabilities_Inf[i].PDC_INF >> 30) & 0x03;

  if ((type == 0) || (type == 2)) // Fixed and Variable Request Data Object
  {
    *(uint32_t*)&(USB302_TX_Buff[7]) |= (PD_Source_Capabilities_Inf[i].PDC_INF & 0x7FFFF);
    PPS_State = 0; //不是pps档
  }

  //USB302_Wite_Reg(0x0B, 0x0F);//全电源  感觉没必要
  USB302_Wite_Reg(0x06, 0x40);//清发送
  USB302_Wite_FIFO(USB302_TX_Buff, 14);
  USB302_Wite_Reg(0x06, 0x05);//开始发

  PD_Msg_ID_ADD();//加包
}


void PD_Show_Service(void)
{
  uint8_t i;
  float cachevolmax, cachevolmin, cachecur;
  char tmp[64];
  if (PD_STEP == 2)
  {
    USB302_Send_Requse(0);//进行一次1包请求, 输出9V
    //Serial.println("Show:");
    udp_debug(" Show:\n");
    uint8_t type = (PD_Source_Capabilities_Inf[i].PDC_INF >> 30) & 0x03;
    for (i = 0; i < PD_Source_Capabilities_Inf_num; i++)
    {
/*      
      if (type == 0) //普通
      {
        udp_debug("Fixed PD--");
        cachevolmax = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 10) & 0x3FF) * 0.05f;
        cachecur = (PD_Source_Capabilities_Inf[i].PDC_INF & 0x3FF) * 0.01f;
        sprintf(tmp, "vol:%f cur:%f\n", cachevolmax, cachecur);
        udp_debug(tmp);
      }
      else if (type == 2) //variable
      {
        udp_debug("Variable");
        cachevolmax = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 20) & 0x3FF) * 0.05f;
        cachevolmin = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 10) & 0x3FF) * 0.05f;
        cachecur = (PD_Source_Capabilities_Inf[i].PDC_INF & 0x3FF) * 0.01f;
        //udp_debug((String)"Voltage:" + cachevolmin + "~" + cachevolmax + ", Current:" + cachecur);
        sprintf(tmp, "vol:%f~%f cur:%f\n", cachevolmin, cachevolmax, cachecur);
        udp_debug(tmp);        
      }
      else if (type == 3) //pps
      {
        udp_debug("PPS");
        cachevolmax = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 17) & 0x7F) * 0.1f;
        cachevolmin = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 8) & 0x7F) * 0.1f;
        cachecur = (PD_Source_Capabilities_Inf[i].PDC_INF & 0x7F) * 0.05f;
        //Serial.println((String)"Voltage:" + cachevolmin + "~" + cachevolmax + ", Current:" + cachecur);
        sprintf(tmp, "vol:%f~%f cur:%f\n", cachevolmin, cachevolmax, cachecur);
        udp_debug(tmp);           
      }
*/      
      if (i < 5)
      {
        udp_debug("Fixed PD--");
        cachevolmax = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 10) & 0x3FF) * 0.05f;
        cachecur = (PD_Source_Capabilities_Inf[i].PDC_INF & 0x3FF) * 0.01f;
        sprintf(tmp, "vol:%f cur:%f\n", cachevolmax, cachecur);
        udp_debug(tmp);
      }
      else
      {
        udp_debug("PPS");
        cachevolmax = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 17) & 0x7F) * 0.1f;
        cachevolmin = ((PD_Source_Capabilities_Inf[i].PDC_INF >> 8) & 0x7F) * 0.1f;
        cachecur = (PD_Source_Capabilities_Inf[i].PDC_INF & 0x7F) * 0.05f;
        //Serial.println((String)"Voltage:" + cachevolmin + "~" + cachevolmax + ", Current:" + cachecur);
        sprintf(tmp, "vol:%f~%f cur:%f\n", cachevolmin, cachevolmax, cachecur);
        udp_debug(tmp);         
      }
    }
    PD_STEP = 3;
  }
}
