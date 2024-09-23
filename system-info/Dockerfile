# 基于官方的 Go 镜像作为基础镜像
FROM golang:1.22-bullseye

WORKDIR /var/www/html

RUN sed -i 's/deb.debian.org/mirrors.tencent.com/g' /etc/apt/sources.list

#设置时区
ENV TZ=Asia/Shanghai
RUN apt-get update \
    && apt-get install libgl1-mesa-glx libglib2.0-0  redis-tools -y \
    && apt-get install python3 python3-dev python3-pip -y \
    && pip config set global.index-url https://pypi.tuna.tsinghua.edu.cn/simple/

# 将当前目录的内容复制到容器的 /app 目录
COPY requirements.txt /var/www/html

# 复制并安装 requirements.txt 中的依赖包
RUN pip install --no-cache-dir -r requirements.txt

#看需求是否将运行程序复制进容器中,不复制的话在创建时候需要-v绑定文件夹

# 设置环境变量 GOPROXY
ENV GO111MODULE=on \
    GOPROXY=https://goproxy.cn,direct \
    CGO_ENABLED=1