LIBRARY ieee;
USE ieee.std_logic_1164.ALL;
USE ieee.numeric_std.ALL;

USE work.itc.ALL;

ENTITY DSD_FPGA_Lab01 IS
    PORT (
        clk : IN STD_LOGIC;
        rst : IN STD_LOGIC;
        sw : IN u8_t;
        seg : OUT u8_t
    );
END DSD_FPGA_Lab01;

ARCHITECTURE arch OF DSD_FPGA_Lab01 IS

BEGIN

    sw_map : FOR i IN 0 TO 7 GENERATE
        seg(i) <= seg(i);
    END GENERATE sw_map;

END arch;