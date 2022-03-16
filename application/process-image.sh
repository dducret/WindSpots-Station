#!/bin/sh
# This will process image
echo " "
echo "${1}"
ROTATE=$4
IMAGE_TEXT=$3
IMAGE_PATH=$2
STATION=$1
IMAGEWIDTH=$(identify -format "%w" $IMAGE_PATH)
IMAGEHEIGHT=$(identify -format "%h" $IMAGE_PATH)
IMAGERES=${IMAGEWIDTH}x${IMAGEHEIGHT}
echo "Image Res: ${IMAGERES}"
IMAGE_GRAB_RES=${IMAGEWIDTH}x${IMAGEHEIGHT}
ROTATE_ABS=${ROTATE#-}
ROTATE_ABS=${ROTATE_ABS#+} 
IMAGE_EXT_HEIGHT=$IMAGEHEIGHT
IMAGE_EXT_RES=${IMAGE_EXT_WIDTH}x${IMAGE_EXT_HEIGHT}
case "$STATION" in
  CHGE051) echo "CHGE05"
    POINTSIZE=54
  ;;
  CHGE991) echo "CHGE99"
    POINTSIZE=54
  ;;
  *) echo "OTHER: ${STATION}"
    POINTSIZE=36
  ;;
esac
echo "Rotate: $ROTATE"
if [ $ROTATE -ne 0 ]; then 
  convert $IMAGE_PATH -rotate $ROTATE +repage -gravity center "${IMAGE_PATH}1"
  mv "${IMAGE_PATH}1" "${IMAGE_PATH}"
  # remove 100 pixel for cropping
  IMAGEHEIGHT=`expr $IMAGEHEIGHT - 100`
  FACTOR=`expr $IMAGEHEIGHT \\* 1000 / 540`
  # echo "Factor: ${FACTOR}"
  # remove 10 pixels per degree in width
  PIXEL=`expr 10 \\* ${FACTOR} / 1000 \\* $ROTATE_ABS`
  # echo "Pixel: ${PIXEL}"
  IMAGEWIDTH=`expr $IMAGEWIDTH - $PIXEL`
  # echo "Image Width base: ${IMAGEWIDTH}" >> /data/sites/www/data/capture/"${STATION}"w.txt
  IMAGEWIDTH=`expr '(' $IMAGEWIDTH / 4 ')' '*' 4`
  # echo "Image Width new: ${IMAGEWIDTH}" >> /data/sites/www/data/capture/"${STATION}"w.txt
  # to keep ratio remove 8 pixels per degree in height
  PIXEL=`expr 15 \\* ${FACTOR} / 1000 \\* $ROTATE_ABS`
  # echo "Pixel: ${PIXEL}"
  IMAGEHEIGHT=`expr $IMAGEHEIGHT - $PIXEL`
  # echo "Image Height base: ${IMAGEHEIGHT}" >> /data/sites/www/data/capture/"${STATION}"w.txt
  IMAGEHEIGHT=`expr $IMAGEHEIGHT / 4 \\* 4`
  # echo "Image Height new: ${IMAGEHEIGHT}" >> /data/sites/www/data/capture/"${STATION}"w.txt 
  IMAGERES=${IMAGEWIDTH}x${IMAGEHEIGHT}
  # echo "Image res: ${IMAGERES}" >> /data/sites/www/data/capture/"${STATION}"w.txt 
else
  FACTOR=`expr $IMAGEHEIGHT \\* 1000 / 540`
  # echo "Factor: ${FACTOR}"
fi
if [ $FACTOR -gt 1000 ]; then
  IMAGEWIDTH=`expr $IMAGEWIDTH \\* $FACTOR / 1000`
fi
# round image size for video conversion
convert $IMAGE_PATH -gravity center -crop "${IMAGERES}+0+0" "${IMAGE_PATH}0"
mogrify -resize $IMAGERES "${IMAGE_PATH}0"
convert "${IMAGE_PATH}0" -gravity south -stroke '#000C' -strokewidth 2  -pointsize ${POINTSIZE} -annotate 0 "$IMAGE_TEXT" \
  -stroke  none -fill white -pointsize ${POINTSIZE} -annotate 0 "$IMAGE_TEXT" "${IMAGE_PATH}1"
# move for windspots v2
mv "${IMAGE_PATH}0"  /data/sites/www/data/capture/"${STATION}"u.jpg
echo $IMAGE_TEXT > /data/sites/www/data/capture/"${STATION}"u.txt
mv "${IMAGE_PATH}1" "${IMAGE_PATH}"
# create 20 image history
# 19
mv /data/sites/www/data/capture/"${STATION}"_18.jpg /data/sites/www/data/capture/"${STATION}"_19.jpg
mv /data/sites/www/data/capture/"${STATION}"u_18.jpg /data/sites/www/data/capture/"${STATION}"u_19.jpg
mv /data/sites/www/data/capture/"${STATION}"u_18.txt /data/sites/www/data/capture/"${STATION}"u_19.txt
# 18
mv /data/sites/www/data/capture/"${STATION}"_17.jpg /data/sites/www/data/capture/"${STATION}"_18.jpg
mv /data/sites/www/data/capture/"${STATION}"u_17.jpg /data/sites/www/data/capture/"${STATION}"u_18.jpg
mv /data/sites/www/data/capture/"${STATION}"u_17.txt /data/sites/www/data/capture/"${STATION}"u_18.txt
# 17
mv /data/sites/www/data/capture/"${STATION}"_16.jpg /data/sites/www/data/capture/"${STATION}"_17.jpg
mv /data/sites/www/data/capture/"${STATION}"u_16.jpg /data/sites/www/data/capture/"${STATION}"u_17.jpg
mv /data/sites/www/data/capture/"${STATION}"u_16.txt /data/sites/www/data/capture/"${STATION}"u_17.txt
# 16
mv /data/sites/www/data/capture/"${STATION}"_15.jpg /data/sites/www/data/capture/"${STATION}"_16.jpg
mv /data/sites/www/data/capture/"${STATION}"u_15.jpg /data/sites/www/data/capture/"${STATION}"u_16.jpg
mv /data/sites/www/data/capture/"${STATION}"u_15.txt /data/sites/www/data/capture/"${STATION}"u_16.txt
# 15 
mv /data/sites/www/data/capture/"${STATION}"_14.jpg /data/sites/www/data/capture/"${STATION}"_15.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_14.jpg /data/sites/www/data/capture/"${STATION}"u_15.jpg 
mv /data/sites/www/data/capture/"${STATION}"u_14.txt /data/sites/www/data/capture/"${STATION}"u_15.txt
# 14 
mv /data/sites/www/data/capture/"${STATION}"_13.jpg /data/sites/www/data/capture/"${STATION}"_14.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_13.jpg /data/sites/www/data/capture/"${STATION}"u_14.jpg 
mv /data/sites/www/data/capture/"${STATION}"u_13.txt /data/sites/www/data/capture/"${STATION}"u_14.txt
# 13 
mv /data/sites/www/data/capture/"${STATION}"_12.jpg /data/sites/www/data/capture/"${STATION}"_13.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_12.jpg /data/sites/www/data/capture/"${STATION}"u_13.jpg 
mv /data/sites/www/data/capture/"${STATION}"u_12.txt /data/sites/www/data/capture/"${STATION}"u_13.txt
# 12 
mv /data/sites/www/data/capture/"${STATION}"_11.jpg /data/sites/www/data/capture/"${STATION}"_12.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_11.jpg /data/sites/www/data/capture/"${STATION}"u_12.jpg 
mv /data/sites/www/data/capture/"${STATION}"u_11.txt /data/sites/www/data/capture/"${STATION}"u_12.txt
# 11
mv /data/sites/www/data/capture/"${STATION}"_10.jpg /data/sites/www/data/capture/"${STATION}"_11.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_10.jpg /data/sites/www/data/capture/"${STATION}"u_11.jpg
mv /data/sites/www/data/capture/"${STATION}"u_10.txt /data/sites/www/data/capture/"${STATION}"u_11.txt
# 10
mv /data/sites/www/data/capture/"${STATION}"_09.jpg /data/sites/www/data/capture/"${STATION}"_10.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_09.jpg /data/sites/www/data/capture/"${STATION}"u_10.jpg
mv /data/sites/www/data/capture/"${STATION}"u_09.txt /data/sites/www/data/capture/"${STATION}"u_10.txt
# 9
mv /data/sites/www/data/capture/"${STATION}"_08.jpg /data/sites/www/data/capture/"${STATION}"_09.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_08.jpg /data/sites/www/data/capture/"${STATION}"u_09.jpg
mv /data/sites/www/data/capture/"${STATION}"u_08.txt /data/sites/www/data/capture/"${STATION}"u_09.txt
# 8
mv /data/sites/www/data/capture/"${STATION}"_07.jpg /data/sites/www/data/capture/"${STATION}"_08.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_07.jpg /data/sites/www/data/capture/"${STATION}"u_08.jpg
mv /data/sites/www/data/capture/"${STATION}"u_07.txt /data/sites/www/data/capture/"${STATION}"u_08.txt
# 7
mv /data/sites/www/data/capture/"${STATION}"_06.jpg /data/sites/www/data/capture/"${STATION}"_07.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_06.jpg /data/sites/www/data/capture/"${STATION}"u_07.jpg
mv /data/sites/www/data/capture/"${STATION}"u_06.txt /data/sites/www/data/capture/"${STATION}"u_07.txt
# 6
mv /data/sites/www/data/capture/"${STATION}"_05.jpg /data/sites/www/data/capture/"${STATION}"_06.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_05.jpg /data/sites/www/data/capture/"${STATION}"u_06.jpg
mv /data/sites/www/data/capture/"${STATION}"u_05.txt /data/sites/www/data/capture/"${STATION}"u_06.txt
# 5
mv /data/sites/www/data/capture/"${STATION}"_04.jpg /data/sites/www/data/capture/"${STATION}"_05.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_04.jpg /data/sites/www/data/capture/"${STATION}"u_05.jpg
mv /data/sites/www/data/capture/"${STATION}"u_04.txt /data/sites/www/data/capture/"${STATION}"u_05.txt
# 4
mv /data/sites/www/data/capture/"${STATION}"_03.jpg /data/sites/www/data/capture/"${STATION}"_04.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_03.jpg /data/sites/www/data/capture/"${STATION}"u_04.jpg
mv /data/sites/www/data/capture/"${STATION}"u_03.txt /data/sites/www/data/capture/"${STATION}"u_04.txt
# 3
mv /data/sites/www/data/capture/"${STATION}"_02.jpg /data/sites/www/data/capture/"${STATION}"_03.jpg  
mv /data/sites/www/data/capture/"${STATION}"u_02.jpg /data/sites/www/data/capture/"${STATION}"u_03.jpg
mv /data/sites/www/data/capture/"${STATION}"u_02.txt /data/sites/www/data/capture/"${STATION}"u_03.txt
# 2
mv /data/sites/www/data/capture/"${STATION}"_01.jpg /data/sites/www/data/capture/"${STATION}"_02.jpg 
mv /data/sites/www/data/capture/"${STATION}"u_01.jpg /data/sites/www/data/capture/"${STATION}"u_02.jpg 
mv /data/sites/www/data/capture/"${STATION}"u_01.txt /data/sites/www/data/capture/"${STATION}"u_02.txt 
# 1
mv /data/sites/www/data/capture/"${STATION}"_00.jpg /data/sites/www/data/capture/"${STATION}"_01.jpg
mv /data/sites/www/data/capture/"${STATION}"u_00.jpg /data/sites/www/data/capture/"${STATION}"u_01.jpg
mv /data/sites/www/data/capture/"${STATION}"u_00.txt /data/sites/www/data/capture/"${STATION}"u_01.txt
# cp /data/sites/www/data/capture/"${STATION}"_0.jpg /data/sites/www/data/capture/"${STATION}"_1.jpg
cp "${IMAGE_PATH}" /data/sites/www/data/capture/"${STATION}".jpg
cp /data/sites/www/data/capture/"${STATION}".jpg /data/sites/www/data/capture/"${STATION}"_00.jpg
mv /data/sites/www/data/capture/"${STATION}"u.jpg /data/sites/www/data/capture/"${STATION}"u_00.jpg
mv /data/sites/www/data/capture/"${STATION}"u.txt /data/sites/www/data/capture/"${STATION}"u_00.txt
# create video
# /data/sites/www/data/capture/video/video.sh ${STATION}