#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
@by https://github.com/neutronstriker/INA226_Driver_Python/blob/master/ina226_driver_aardvark.py
This a fork of the INA226 I2C python driver from neutronstriker
changed for python3

Last change:

### Original comment ###

Created on Wed May 17 23:45:12 2017

Decription: INA226 I2C based Bi-directional Current/Power Sensor Driver for Python

@author: Srinivas Nistala
@https://github.com/neutronstriker

Ported from Arduino-INA226
https://github.com/jarzebski/Arduino-INA226
Original Author:Korneliusz Jarzebski
Contributor: Srinivas Nistala (aka neutronstriker)

This program is free software: you can redistribute it and/or modify
it under the terms of the version 3 GNU General Public License as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

"""

import time
import math
import ctypes
import sys

PYTHON_SMBUS_LIB_PRESENT = True
PYTHON_AARDVARK_LIB_PRESENT = True

try:
    import smbus
except ImportError as e:
    PYTHON_SMBUS_LIB_PRESENT = False

try:
    import pyaardvark
except ImportError as e:
    PYTHON_AARDVARK_LIB_PRESENT = False

INA226_ADDRESS = (0x40)

INA226_REG_CONFIG = (0x00)
INA226_REG_SHUNTVOLTAGE = (0x01)
INA226_REG_BUSVOLTAGE = (0x02)
INA226_REG_POWER = (0x03)
INA226_REG_CURRENT = (0x04)
INA226_REG_CALIBRATION = (0x05)
INA226_REG_MASKENABLE = (0x06)
INA226_REG_ALERTLIMIT = (0x07)

INA226_BIT_SOL = (0x8000)
INA226_BIT_SUL = (0x4000)
INA226_BIT_BOL = (0x2000)
INA226_BIT_BUL = (0x1000)
INA226_BIT_POL = (0x0800)
INA226_BIT_CNVR = (0x0400)
INA226_BIT_AFF = (0x0010)
INA226_BIT_CVRF = (0x0008)
INA226_BIT_OVF = (0x0004)
INA226_BIT_APOL = (0x0002)
INA226_BIT_LEN = (0x0001)

# enum replacement, but not truly
# now replaced class by dict because it can give me back keys
ina226_averages_t = dict(
    INA226_AVERAGES_1=0b000,
    INA226_AVERAGES_4=0b001,
    INA226_AVERAGES_16=0b010,
    INA226_AVERAGES_64=0b011,
    INA226_AVERAGES_128=0b100,
    INA226_AVERAGES_256=0b101,
    INA226_AVERAGES_512=0b110,
    INA226_AVERAGES_1024=0b111)

ina226_busConvTime_t = dict(
    INA226_BUS_CONV_TIME_140US=0b000,
    INA226_BUS_CONV_TIME_204US=0b001,
    INA226_BUS_CONV_TIME_332US=0b010,
    INA226_BUS_CONV_TIME_588US=0b011,
    INA226_BUS_CONV_TIME_1100US=0b100,
    INA226_BUS_CONV_TIME_2116US=0b101,
    INA226_BUS_CONV_TIME_4156US=0b110,
    INA226_BUS_CONV_TIME_8244US=0b111)

ina226_shuntConvTime_t = dict(
    INA226_SHUNT_CONV_TIME_140US=0b000,
    INA226_SHUNT_CONV_TIME_204US=0b001,
    INA226_SHUNT_CONV_TIME_332US=0b010,
    INA226_SHUNT_CONV_TIME_588US=0b011,
    INA226_SHUNT_CONV_TIME_1100US=0b100,
    INA226_SHUNT_CONV_TIME_2116US=0b101,
    INA226_SHUNT_CONV_TIME_4156US=0b110,
    INA226_SHUNT_CONV_TIME_8244US=0b111)

ina226_mode_t = dict(
    INA226_MODE_POWER_DOWN=0b000,
    INA226_MODE_SHUNT_TRIG=0b001,
    INA226_MODE_BUS_TRIG=0b010,
    INA226_MODE_SHUNT_BUS_TRIG=0b011,
    INA226_MODE_ADC_OFF=0b100,
    INA226_MODE_SHUNT_CONT=0b101,
    INA226_MODE_BUS_CONT=0b110,
    INA226_MODE_SHUNT_BUS_CONT=0b111)

# available options are 'AARDVARK','SBC_LINUX_SMBUS'
I2C_DRIVER = 'AARDVARK'
# other I2C options
I2C_DEFAULT_CLK_KHZ = 100
I2C_DEFAULT_BUS_NUMBER = 0


class ina226:
    def __init__(self, ina226_addr=INA226_ADDRESS, i2c_bus_number=I2C_DEFAULT_BUS_NUMBER,
                 i2c_clk_Khz=I2C_DEFAULT_CLK_KHZ, i2c_driver_type=I2C_DRIVER):

        if PYTHON_AARDVARK_LIB_PRESENT is False and PYTHON_SMBUS_LIB_PRESENT is False:
            print("Neither PYAARDVARK nor SMBUS lib is installed, Please install an appropriate one and try again.")
            sys.exit(0)

        if i2c_driver_type == 'AARDVARK':
            if PYTHON_AARDVARK_LIB_PRESENT is False:
                print('PYAARDVARK Driver is not installed, Please install and Try again.')
                sys.exit(0)
            self.i2c_bus = pyaardvark.open(i2c_bus_number)
            self.i2c_bus.i2c_bitrate = i2c_clk_Khz
            self.readRegister16 = self.readRegister16_AARDVARK
            self.writeRegister16 = self.writeRegister16_AARDVARK

        elif i2c_driver_type == 'SBC_LINUX_SMBUS':
            if PYTHON_SMBUS_LIB_PRESENT is False:
                print('PYTHON SMBUS Driver is not installed, Please install and Try again.')
                sys.exit(0)
            self.i2c_bus = smbus.SMBus(i2c_bus_number)
            self.readRegister16 = self.readRegister16_SMBUS
            self.writeRegister16 = self.writeRegister16_SMBUS
            if i2c_clk_Khz != I2C_DEFAULT_CLK_KHZ:
                print('Python SMBUS linux driver doesn\'t provide I2C CLK Freq Manipulation support yet,')
                print('So Ignoring i2c_clk_khz param and using default.')
        else:
            print('Unknown I2C DRIVER Specified, available Options are : AARDVARK, SBC_LINUX_SMBUS')

        self.ina226_address = ina226_addr
        self.vBusMax = 36
        self.vShuntMax = 0.08192
        self.rShunt = 0.1
        self.currentLSB = 0
        self.powerLSB = 0
        self.iMaxPossible = 0

    # not using with statement related code yet

    # this causes some issue may be because when exception occurs I am manually calling
    # self.close() and even this function tries to call the same. Need to check.
    # def __del__(self):
    #    self.close()

    def close(self):
        self.i2c_bus.close()

    def readRegister16_SMBUS(self, register):
        # higher_byte = self.i2c_bus.read_byte_data(self.ina226_address,register)
        # lower_byte = self.i2c_bus.read_byte_data(self.ina226_address,register+1)
        data = self.i2c_bus.read_i2c_block_data(self.ina226_address, register, 2)
        higher_byte = data[0]
        lower_byte = data[1]
        # there is still some issue in read which we need to fix, we are not able to print negative current--done--fixed using ctypes int16 return
        word_data = higher_byte << 8 | lower_byte
        # return word_data
        return ctypes.c_int16(word_data).value

    def writeRegister16_SMBUS(self, register, dataWord):
        higher_byte = (dataWord >> 8) & 0xff
        lower_byte = dataWord & 0xff  # truncating the dataword to byte
        self.i2c_bus.write_i2c_block_data(self.ina226_address, register, [higher_byte, lower_byte])

    def readRegister16_AARDVARK(self, register):
        # higher_byte = self.i2c_bus.read_byte_data(self.ina226_address,register)
        # lower_byte = self.i2c_bus.read_byte_data(self.ina226_address,register+1)
        # data = self.i2c_bus.read_i2c_block_data(self.ina226_address,register,2)

        register_addr_str = chr(register)

        byte_char_data = self.i2c_bus.i2c_master_write_read(self.ina226_address, register_addr_str, 2)

        data = [ord(b) for b in byte_char_data]

        higher_byte = data[0]
        lower_byte = data[1]
        # there is still some issue in read which we need to fix, we are not able to print negative current--done--fixed using ctypes int16 return
        word_data = higher_byte << 8 | lower_byte
        # return word_data
        return ctypes.c_int16(word_data).value
        # if this does not work as expected than we should try to read bytes and they convert into word as in Arduino
        # return self.i2c_bus.read_word_data(self.ina226_address,register)

    def writeRegister16_AARDVARK(self, register, dataWord):
        higher_byte = (dataWord >> 8) & 0xff
        lower_byte = dataWord & 0xff  # truncating the dataword to byte

        data = (register, higher_byte, lower_byte)
        data = ''.join(chr(c) for c in data)

        self.i2c_bus.i2c_master_write(self.ina226_address, data)

        # self.i2c_bus.write_i2c_block_data(self.ina226_address,register,[higher_byte,lower_byte])

        # doesn't work
        # self.i2c_bus.write_byte_data(self.ina226_address,register,higher_byte)
        # self.i2c_bus.write_byte_data(self.ina226_address,register+1,lower_byte)
        # if this does not work as expected than we should try to read bytes and they convert into word as in Arduino
        # self.i2c_bus.write_word_data(self.ina226_address,register,dataWord)

    def configure(self, avg=ina226_averages_t['INA226_AVERAGES_1'],
                  busConvTime=ina226_busConvTime_t['INA226_BUS_CONV_TIME_1100US'],
                  shuntConvTime=ina226_shuntConvTime_t['INA226_SHUNT_CONV_TIME_1100US'],
                  mode=ina226_mode_t['INA226_MODE_SHUNT_BUS_CONT']):
        config = 0
        config |= (avg << 9 | busConvTime << 6 | shuntConvTime << 3 | mode)
        self.writeRegister16(INA226_REG_CONFIG, config)
        return True

    def calibrate(self, rShuntValue=0.1, iMaxExcepted=2):
        self.rShunt = rShuntValue

        self.iMaxPossible = self.vShuntMax / self.rShunt

        minimumLSB = float(iMaxExcepted) / 32767

        print("minimumLSB:" + str(minimumLSB))

        self.currentLSB = int((minimumLSB * 100000000))
        print("currentLSB:" + str(self.currentLSB))
        self.currentLSB /= 100000000.0
        self.currentLSB /= 0.0001
        self.currentLSB = math.ceil(self.currentLSB)
        self.currentLSB *= 0.0001

        self.powerLSB = self.currentLSB * 25

        print("powerLSB:" + str(self.powerLSB))
        print("rshunt:" + str(self.rShunt))

        calibrationValue = int(((0.00512) / (
                self.currentLSB * self.rShunt)))  # if we get error need to convert this to unsigned int 16 bit instead

        self.writeRegister16(INA226_REG_CALIBRATION, calibrationValue)

        return True

    def getAverages(self):
        value = self.readRegister16(INA226_REG_CONFIG)
        value &= 0b0000111000000000
        value >>= 9
        return value

    def getMaxPossibleCurrent(self):
        return (self.vShuntMax / self.rShunt)

    def getMaxCurrent(self):
        maxCurrent = (self.currentLSB * 32767)
        maxPossible = self.getMaxPossibleCurrent()

        if maxCurrent > maxPossible:
            return maxPossible
        else:
            return maxCurrent

    def getMaxShuntVoltage(self):
        maxVoltage = self.getMaxCurrent() * self.rShunt
        if maxVoltage >= self.vShuntMax:
            return self.vShuntMax
        else:
            return maxVoltage

    def getMaxPower(self):
        return (self.getMaxCurrent() * self.vBusMax)

    def readBusPower(self):
        return (self.readRegister16(INA226_REG_POWER) * self.powerLSB)

    def readShuntCurrent(self):
        return (self.readRegister16(INA226_REG_CURRENT) * self.currentLSB)

    def readShuntVoltage(self):
        voltage = self.readRegister16(INA226_REG_SHUNTVOLTAGE)
        return (voltage * 0.0000025)

    def readBusVoltage(self):
        voltage = self.readRegister16(INA226_REG_BUSVOLTAGE)
        return (voltage * 0.00125)

    def getBusConversionTime(self):
        value = self.readRegister16(INA226_REG_CONFIG)
        value &= 0b0000000111000000
        value >>= 6
        return value

    def getShuntConversionTime(self):
        value = self.readRegister16(INA226_REG_CONFIG)
        value &= 0b0000000000111000
        value >>= 3
        return value

    def getMode(self):
        value = self.readRegister16(INA226_REG_CONFIG)
        value &= 0b0000000000000111
        return value

    def setMaskEnable(self, mask):
        self.writeRegister16(INA226_REG_MASKENABLE, mask)

    def getMaskEnable(self):
        return self.readRegister16(INA226_REG_MASKENABLE)

    def enableShuntOverLimitAlert(self):
        self.writeRegister16(INA226_REG_MASKENABLE, INA226_BIT_SOL)

    def enableBusOverLimitAlert(self):
        self.writeRegister16(INA226_REG_MASKENABLE, INA226_BIT_BOL)

    def enableBusUnderLimitAlert(self):
        self.writeRegister16(INA226_REG_MASKENABLE, INA226_BIT_BUL)

    def enableOverPowerLimitAlert(self):
        self.writeRegister16(INA226_REG_MASKENABLE, INA226_BIT_POL)

    def enableConversionReadyAlert(self):
        self.writeRegister16(INA226_REG_MASKENABLE, INA226_BIT_CNVR)

    def setBusVoltageLimit(self, voltage):
        value = voltage / 0.00125
        self.writeRegister16(INA226_REG_ALERTLIMIT, value)

    def setShuntVoltageLimit(self, voltage):
        value = voltage * 25000
        self.writeRegister16(INA226_REG_ALERTLIMIT, value)

    def setPowerLimit(self, watts):
        value = watts / self.powerLSB
        self.writeRegister16(INA226_REG_ALERTLIMIT, value)

    def setAlertInvertedPolarity(self, inverted):
        temp = self.getMaskEnable()

        if (inverted):
            temp |= INA226_BIT_APOL
        else:
            temp &= ~INA226_BIT_APOL
        self.setMaskEnable(temp)

    def setAlertLatch(self, latch):
        temp = self.getMaskEnable()
        if (latch):
            temp |= INA226_BIT_LEN
        else:
            temp &= ~INA226_BIT_LEN
        self.setMaskEnable(temp)

    def isMathOverflow(self):
        return ((self.getMaskEnable() & INA226_BIT_OVF) == INA226_BIT_OVF)

    def isAlert(self):
        return ((self.getMaskEnable() & INA226_BIT_AFF) == INA226_BIT_AFF)


# -----------------------Demo Program--------------------------------------

def demo():
    try:
        print("Configuring INA226..")
        iSensor = ina226(INA226_ADDRESS, 0)
        iSensor.configure(avg=ina226_averages_t['INA226_AVERAGES_4'], )
        iSensor.calibrate(rShuntValue=0.02, iMaxExcepted=2)

        time.sleep(1)

        print("Configuration Done")

        current = iSensor.readShuntCurrent()

        print("Current Value is " + str(current) + "A")

        print("Mode is " + str(hex(iSensor.getMode())))

        while True:
            print("Current: " + str(round(iSensor.readShuntCurrent(), 3)) + "A" + ", Voltage: " + str(
                round(iSensor.readBusVoltage(), 3)) + "V" + ", Power:" + str(round(iSensor.readBusPower(), 3)) + "W")
            # print "ShuntBus_Voltage: "+str(iSensor.readShuntVoltage())
            time.sleep(0.2)

    except KeyboardInterrupt as e:
        print('\nCTRL^C received, Terminating..')
        iSensor.close()

    except Exception as e:
        print("There has been an exception, Find detais below:")
        print(str(e))
        iSensor.close()
