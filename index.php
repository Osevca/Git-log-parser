<?php

interface CommitMessageParser
{
    public function parse(string $message): CommitMessage;
}

interface CommitMessage
{
    public function getTitle(): string;
    public function getTaskId(): ?int;
    public function getTags(): array;
    public function getDetails(): array;
    public function getBCBreaks(): array;
    public function getTodos(): array;
}

class SimpleCommitMessage implements CommitMessage
{
    private $title;
    private $taskId;
    private $tags;
    private $details;
    private $bcBreaks;
    private $todos;

    public function __construct(string $title, ?int $taskId, array $tags, array $details, array $bcBreaks, array $todos)
    {
        $this->title = $title;
        $this->taskId = $taskId;
        $this->tags = $tags;
        $this->details = $details;
        $this->bcBreaks = $bcBreaks;
        $this->todos = $todos;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTaskId(): ?int
    {
        return $this->taskId;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getBCBreaks(): array
    {
        return $this->bcBreaks;
    }

    public function getTodos(): array
    {
        return $this->todos;
    }
}

class SimpleCommitMessageParser implements CommitMessageParser
{
    public function parse($message): CommitMessage
    {
        $title = '';
        $taskId = null;
        $tags = [];
        $details = [];
        $bcBreaks = [];
        $todos = [];

        $lines = explode("\n", $message);

        foreach ($lines as $line) {
            if (!$title && trim($line)) {
                if (preg_match('/.*?([A-Z].*)/', $line, $matches)) {
                    $title = trim($matches[1]);
                }
            }

            if (strpos($line, '#') !== false) {
                preg_match('/#(\d+)/', $line, $matches);
                if (!empty($matches[1])) {
                    $taskId = (int)$matches[1];
                }
            }

            if (strpos($line, '[') !== false && strpos($line, ']') !== false) {
                preg_match_all('/\[(.*?)\]/', $line, $matches);
                if (!empty($matches[1])) {
                    $tags = array_merge($tags, $matches[1]);
                }
            }

            if (strpos($line, 'BC:') !== false) {
                $bcBreaks[] = trim(substr($line, strpos($line, 'BC:') + 3));
            }

            if (strpos($line, 'TODO:') !== false) {
                $todos[] = trim(substr($line, strpos($line, 'TODO:') + 5));
            }

            if (strpos($line, '*') === 0) {
                $details[] = trim(substr($line, 1));
            }
        }
        if (empty($title) || empty($details) || empty($taskId) || empty($tags) || empty($bcBreaks) || empty($todos)) {
            throw new Exception("Invalid commit message format.");
        }


        return new SimpleCommitMessage($title, $taskId, $tags, $details, $bcBreaks, $todos);
    }
}
try {
    $parser = new SimpleCommitMessageParser();
    // $message = "[add] [feature] @core  Integrovat Premier: export objednávek\n* Export objednávek cronem co hodinu.\n* Export probíhá v dávkách.\nBC: Refaktorovaný BaseImporter.\nBC: ...\nFeature: Nový logger.\n. \nTODO: Refactoring autoemail modulu.\n#1234";

    // $message = "[bugfix] Opravit chybu v module exportu.* Opraveni chyby spocivajici v nespravnem formatovani dat. BC: Zmeny v API endpointech. TODO: Pridat dalsi testovaci pripady.";
    $message = "[feature] Pridat novou funkci pro export dat.\n* Implementace nove funkcionality pro export dat do CSV.\n*Optimalizace vzkonu exportu.\nBC: Zmeny ve formatu exportovanych dat.\n#12345.\nTODO: Aktualizovat dokumentaci.";

    $commitMessage = $parser->parse($message);

    $title = $commitMessage->getTitle();
    $taskId = $commitMessage->getTaskId();
    $tags = $commitMessage->getTags();
    $details = $commitMessage->getDetails();
    $bcBreaks = $commitMessage->getBCBreaks();
    $todos = $commitMessage->getTodos();


    echo "<strong>Title:</strong><br>- $title<br>";
    echo "<strong>Task ID:</strong><br> -" . ($taskId !== null ? $taskId : "N/A");
    echo "<br><strong>Tags: </strong>";
    foreach ($tags as $tag) {
        echo "<br>  - $tag";
    }

    echo "<br><strong>Details:</strong>";
    foreach ($details as $detail) {
        echo "<br>  - $detail";
    }

    echo "<br><strong>BC Breaks:</strong>";
    foreach ($bcBreaks as $bcBreak) {
        echo "<br> - $bcBreak";
    }

    echo "<br><strong>TODOs:</strong>";
    foreach ($todos as $todo) {
        echo "<br> - $todo";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
