set LAST_DIR=%cd%
cd /d %~dp0

set ROOT_DIR=%cd%\..\..
set FRAME_DIR=%ROOT_DIR%/frame
set FRAME_REPOSITORY=https://github.com/smarty-kiki/frame.git

cd %ROOT_DIR%
git clone %FRAME_REPOSITORY%

cd %LAST_DIR%