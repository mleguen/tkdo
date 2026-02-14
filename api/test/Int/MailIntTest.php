<?php

declare(strict_types=1);

namespace Test\Int;

class MailIntTest extends IntTestCase
{
    /**
     * Test that PHP mail() delivers to MailDev SMTP on port 1025
     */
    public function testSmtpDeliveryToMaildev(): void
    {
        $to = 'test-smtp@example.com';
        $subject = 'Test SMTP delivery';
        $message = 'This is a test message to verify SMTP delivery to MailDev.';

        $result = mail($to, $subject, $message, [
            'From' => 'noreply@tkdo',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);

        $this->assertTrue($result, 'PHP mail() should return true when SMTP delivery succeeds');

        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertMessageRecipientsContains($to, $emailsRecus[0]);
        $this->assertEquals($subject, $emailsRecus[0]['subject']);
    }

    /**
     * Test UTF-8 subject encoding with long French text (>76 chars triggers MIME folding)
     */
    public function testUtf8LongSubjectEncoding(): void
    {
        $to = 'test-utf8-long@example.com';
        $longSubject = "Participation au tirage cadeaux No\u{00EB}l \u{2014} r\u{00E9}capitulatif d\u{00E9}taill\u{00E9} des \u{00E9}changes pr\u{00E9}vus cette ann\u{00E9}e";

        $this->assertGreaterThan(76, strlen($longSubject), 'Subject must exceed 76 chars to test MIME folding');

        $result = mail($to, mb_encode_mimeheader($longSubject, 'UTF-8'), 'Test body', [
            'From' => 'noreply@tkdo',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);

        $this->assertTrue($result);

        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertEquals($longSubject, $emailsRecus[0]['subject']);
    }

    /**
     * Test UTF-8 subject encoding with emoji characters
     */
    public function testUtf8EmojiSubjectEncoding(): void
    {
        $to = 'test-emoji@example.com';
        $emojiSubject = "\u{1F384} Tirage cadeaux No\u{00EB}l \u{1F381}";

        $result = mail($to, mb_encode_mimeheader($emojiSubject, 'UTF-8'), 'Test body', [
            'From' => 'noreply@tkdo',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);

        $this->assertTrue($result);

        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertEquals($emojiSubject, $emailsRecus[0]['subject']);
    }

    /**
     * Test UTF-8 subject encoding with mixed character sets (French, Cyrillic, CJK)
     */
    public function testUtf8MixedCharacterSetSubjectEncoding(): void
    {
        $to = 'test-mixed@example.com';
        $mixedSubject = "No\u{00EB}l \u{2014} \u{0420}\u{043E}\u{0436}\u{0434}\u{0435}\u{0441}\u{0442}\u{0432}\u{043E} \u{2014} \u{30AF}\u{30EA}\u{30B9}\u{30DE}\u{30B9}";

        $result = mail($to, mb_encode_mimeheader($mixedSubject, 'UTF-8'), 'Test body', [
            'From' => 'noreply@tkdo',
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);

        $this->assertTrue($result);

        $emailsRecus = $this->depileDerniersEmailsRecus();
        $this->assertCount(1, $emailsRecus);
        $this->assertEquals($mixedSubject, $emailsRecus[0]['subject']);
    }
}
