require 'rubygems'
require 'rake/clean'
require 'nokogiri'

PACKAGE = []
PACKAGE << "src/machinist"

pearspec = {
    :version => "0.0.1",
    :stability => "beta",
    :name => "machinist",
    :channel => "pear.something-awesome.com",
    :summary => "PHP Test Fixtures",
    :description => "Convenient test fixtures for PHP 5.3.\n" +
        "\tInspired by Phactory, Factory_Girl, and Machinist.",
    :notes => "Nothing of importance.",
    :license => {
        :name => "MIT",
        :uri => "http://www.opensource.org/licenses/mit-license.php"
    },
    :developers => [
        {
            :name => "Stephan Soileau",
            :user => "stephan.soileau",
            :email => "ssoileau@gmail.com",
            :active => true,
            :role => :lead
        }
    ],
    :dependencies => {
        :php => {
            :min => '5.3',
        },
        :pearinstaller => {
            :min => "1.9.0"
        }
    }
}

def build_file_list(dir)
  files = []
  Dir[File.join(dir,'*')].each do |entry|
    if (FileTest.directory?(entry)) then
      files.concat build_file_list(entry)
    else
      files << entry.to_s.gsub(/\.\//,'')
    end
  end
  files
end
namespace :test do
  desc "Run all phpunit tests."
  task :phpunit do
    d = FileUtils.pwd
    Dir.chdir File.join(File.dirname(__FILE__), "test")
    puts %x(phpunit .)
    @test_status = $?
    Dir.chdir(d)
  end
  task :default => [:phpunit]
end
task :test => ["test:default"]
namespace :pear do
  task :packagedir do
    FileUtils.rm_rf('package') if File.exists?('package')
    FileUtils.mkdir 'package'
    PACKAGE.each { |dir|
      FileUtils.cp_r dir, 'package'
    }
    odir =  FileUtils.pwd
    Dir.chdir("package")
    @list = build_file_list('.')
    Dir.chdir(odir)
  end
  desc "Builds package.xml file for pear"
  task :xml => [:packagedir] do
    @xml_doc = Nokogiri::XML::Builder.new(:encoding => 'UTF-8') do |xml|
      xml.package(
          "xmlns" => "http://pear.php.net/dtd/package-2.0",
          "xmlns:tasks" => "http://pear.php.net/dtd/tasks-1.0",
          "xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
          "version" => "2.0",
          "xsi:schemaLocation" => "http://pear.php.net/dtd/tasks-1.0   http://pear.php.net/dtd/tasks-1.0.xsd   http://pear.php.net/dtd/package-2.0   http://pear.php.net/dtd/package-2.0.xsd"
      ) do |package|
        package.name pearspec[:name]
        package.channel pearspec[:channel]
        package.summary pearspec[:summary]
        package.description pearspec[:description]
        pearspec[:developers].each do |dev|
          package.send(dev[:role]) do |t|
            t.name dev[:name]
            t.user dev[:user]
            t.email dev[:email]
            t.active "yes" if dev[:active]
            t.active "no" unless dev[:active]
          end
        end
        package.date Time.now.strftime("%F")
        package.time Time.now.strftime("%H:%M:%S")
        package.version do |v|
          v.release pearspec[:version]
          v.api pearspec[:version]
        end
        package.stability do |s|
          s.release pearspec[:stability]
          s.api pearspec[:stability]
        end
        package.license(pearspec[:license][:name], :uri => pearspec[:license][:uri])
        package.notes pearspec[:notes]
        package.contents do |c|
          c.dir("baseinstalldir" => "/", "name" => "/") do |d|
            @list.each do |f|
              c.file("baseinstalldir" => "/", "name" => f, :role=>"php")
            end
          end
        end
        package.dependencies do |d|
          d.required do |r|
            r.php do |y|
              y.min pearspec[:dependencies][:php][:min] unless pearspec[:dependencies][:php][:min].nil?
              y.max pearspec[:dependencies][:php][:max] unless pearspec[:dependencies][:php][:max].nil?
            end
            r.pearinstaller do|y|
              y.min pearspec[:dependencies][:pearinstaller][:min] unless pearspec[:dependencies][:pearinstaller][:min].nil?
            end
          end
        end
        package.phprelease
      end
    end
  end
  desc "Package everything up all nice"
  task :package => ["test:phpunit", :xml] do
    if (@test_status.to_i == 0) then
      File.open(File.join("package","package.xml"), "w") {|f| f.write(@xml_doc.to_xml(:indent => 4)) }
      puts %x(cd package && pear package && mv #{pearspec[:name]}-#{pearspec[:version]}.tgz ../)
    else
      puts "Stopping build.. Unit tests failed."
    end
    FileUtils.rm_rf('package') if File.exists?('package')
  end
end


