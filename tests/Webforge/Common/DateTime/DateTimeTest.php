<?php

namespace Webforge\Common\DateTime;

class DateTimeTest extends \Webforge\Common\TestCase {
    
  public function testYesterday() {
    $now = time();
    $yesterday = $now-24*60*60;
    $beforeYesterday = $now-48*60*60;
    
    $now = DateTime::factory($now);
    $yesterday = DateTime::factory($yesterday);
    $beforeYesterday = DateTime::factory($beforeYesterday);
    
    $this->assertTrue($yesterday->isYesterday());
    $this->assertTrue($yesterday->isYesterday($now));
    
    $this->assertFalse($beforeYesterday->isYesterday());
    $this->assertFalse($beforeYesterday->isYesterday($now));

    $now->add(DateInterval::createFromDateString('1 DAY'));
    $this->assertFalse($yesterday->isYesterday($now));
  }
  
  public function testToday() {
    $now = DateTime::now();
    $this->assertTrue($now->isToday());
  }
  
  public function testisWeekDay() {
    $now = DateTime::parse('d.m.Y H:i','5.1.2012 12:00');
    $we = DateTime::parse('d.m.Y H:i', '4.1.2012 12:00');
    $mo = DateTime::parse('d.m.Y H:i', '2.1.2012 12:00');
    $su = DateTime::parse('d.m.Y H:i', '8.1.2012 12:00');
    
    
    $this->assertTrue($we->isWeekDay($now));
    $this->assertTrue($mo->isWeekDay($now));
    $this->assertTrue($su->isWeekDay($now));
    
    $now = DateTime::parse('d.m.Y','10.1.2012');
    $this->assertFalse($we->isWeekDay($now));
    $this->assertFalse($mo->isWeekDay($now));
    $this->assertFalse($su->isWeekDay($now));
  }

  /**
   * @dataProvider provideFormatSpan
   */
  public function testGetWeekday($day, $date, $assertion) {
    $this->assertEquals($assertion, $date->getWeekday($day)->format('d.m.Y'));
  }
  
  public function testParseFromRFC1123() {
    $this->assertInstanceof('Webforge\Common\DateTime\DateTime', DateTime::parse(DateTime::RFC1123 , 'Thu, 10 Nov 2011 07:28:18 GMT'));
  }


  public function testCoolSettersAndGetters() {
    $day = 12;
    $month = 1;
    $year = 2012;
    
    $date = new DateTime('12.1.2012');
    $this->assertSame($day,$date->getDay());
    $this->assertSame($month,$date->getMonth());
    $this->assertSame($year,$date->getYear());
    
    $date->setYear(1940);
    $this->assertSame($day,$date->getDay());
    $this->assertSame($month,$date->getMonth());
    $this->assertSame(1940,$date->getYear());
  }
  
  /**
   * @expectedException InvalidArgumentException
   */
  public function testSetYearBecomesInt() {
    $date = DateTime::now();
    $date->setYear('2011');
  }
  
  
  public function provideFormatSpan() {
    return Array(
      
      // 14.03. ist der Montag der Woche
      array(DateTime::MON, new DateTime('14.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('14.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('14.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('14.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('14.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('14.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('14.03.2011'), '20.03.2011'),

      // 15.03. ist der Dienstag der Woche
      array(DateTime::MON, new DateTime('15.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('15.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('15.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('15.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('15.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('15.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('15.03.2011'), '20.03.2011'),
      
      // 16.03. ist der Mittwoch der Woche
      array(DateTime::MON, new DateTime('16.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('16.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('16.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('16.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('16.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('16.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('16.03.2011'), '20.03.2011'),

      // 17.03. ist der Donnerstag der Woche
      array(DateTime::MON, new DateTime('17.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('17.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('17.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('17.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('17.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('17.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('17.03.2011'), '20.03.2011'),


      // 18.03. ist der Freitag der Woche
      array(DateTime::MON, new DateTime('18.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('18.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('18.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('18.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('18.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('18.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('18.03.2011'), '20.03.2011'),


      // 19.03. ist der Samstag der Woche
      array(DateTime::MON, new DateTime('19.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('19.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('19.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('19.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('19.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('19.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('19.03.2011'), '20.03.2011'),


      // 20.03. ist der Sonntag der Woche
      array(DateTime::MON, new DateTime('20.03.2011'), '14.03.2011'),
      array(DateTime::TUE, new DateTime('20.03.2011'), '15.03.2011'),
      array(DateTime::WED, new DateTime('20.03.2011'), '16.03.2011'),
      array(DateTime::THU, new DateTime('20.03.2011'), '17.03.2011'),
      array(DateTime::FRI, new DateTime('20.03.2011'), '18.03.2011'),
      array(DateTime::SAT, new DateTime('20.03.2011'), '19.03.2011'),
      array(DateTime::SUN, new DateTime('20.03.2011'), '20.03.2011'),


      // 17.03. ist der Montag der folgenden Woche
      array(DateTime::MON, new DateTime('21.03.2011'), '21.03.2011'),
      array(DateTime::TUE, new DateTime('21.03.2011'), '22.03.2011'),
      array(DateTime::WED, new DateTime('21.03.2011'), '23.03.2011'),
      array(DateTime::THU, new DateTime('21.03.2011'), '24.03.2011'),
      array(DateTime::FRI, new DateTime('21.03.2011'), '25.03.2011'),
      array(DateTime::SAT, new DateTime('21.03.2011'), '26.03.2011'),
      array(DateTime::SUN, new DateTime('21.03.2011'), '27.03.2011'),
    );
  }
}
?>