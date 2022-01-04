<?php


namespace summer;


class Config
{
    /**
     * 配置文件路径
     * @var string
     */
    public string $path;

    public function __construct()
    {
        if (!$this->loadConfig(ROOT_PATH . SRC_PATH . CONFIG_PATH)) throw new Exception("501", "加载配置文件失败");
        $this->get();
    }

    public function get(){
        $s = yaml_parse_file($this->path);
        var_dump((object)$s);
    }

    /**
     * 加载配置文件
     */
    private function loadConfig(string $path): bool
    {
        $filenames = scandir($path);
        foreach ($filenames as $filename) {
            if (is_dir($filename)) continue;
            if (str_ends_with($filename, ".yaml")) {
                $this->path = $path . $filename;
                return true;
            }
        }
        return false;
    }
}