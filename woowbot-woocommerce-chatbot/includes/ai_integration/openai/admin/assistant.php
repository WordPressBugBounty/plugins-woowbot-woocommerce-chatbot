
<div class="row my-4">
    <div  class="col-md-12">
        <?php esc_html_e('GPT Assistant is available with the ','chatbot');?>
        <a href="https://www.woowbot.pro/pricing/"><?php esc_html_e('WoowBot Pro Professional','chatbot'); ?></a>
        <?php esc_html_e(' and ','chatbot'); ?>
        <a href="https://www.woowbot.pro/pricing/"><?php esc_html_e('Master','chatbot'); ?></a>
        <?php esc_html_e(' Licenses','chatbot'); ?>
    </div>
</div>
<div class="alert alert-info" role="alert">
        <p><b><?php echo esc_html__('Fine Tuning VS GPT Assistants:', 'chatbot'); ?></b></p>
        <p>
            <?php echo esc_html__('We suggest using GPT Assistants instead of Fine Tuning as Fine Tuning requires a lot of properly formatted data and GPT Assistants are easier to set up. You can still use your website data to train the bot.', 'chatbot'); ?>
        </p></br>
        <p>
        <b><?php echo esc_html__('Setting up GPT Assistants:', 'chatbot'); ?></b>
        <ol>
            <li><?php echo esc_html__('Go to the GPT training with Website Data tab. Select all the Post types you need and press the button Convert Data for Assistant. Once it is done, download the data file in JSON format', 'chatbot'); ?></li>
            <li><?php echo esc_html__('Go to ', 'chatbot'); ?><a href="https://platform.openai.com/playground/assistants" target="_blank"><?php echo esc_html__('Assistants playground', 'chatbot'); ?></a><?php echo esc_html__(' and create a new Assistant', 'chatbot'); ?></li>
            <li><?php echo esc_html__('Upload the JSON file you downloaded and give the GPT an Instruction similar to the one below but customize it to your website URL:', 'chatbot'); ?>
            <p><?php echo esc_html__('Your name is Carry. You are a ChatBot assistant for the website ', 'chatbot'); ?><a href="https://www.quantumcloud.com" target="_blank"><?php echo esc_html__('https://www.quantumcloud.com. ', 'chatbot'); ?></a><?php echo esc_html__('Use the website data and the uploaded file to answer any questions from our website users.', 'chatbot'); ?></p>
            </li>
            <li><?php echo esc_html__('Copy the Assistants ID and paste under the GPT Assistants tab under the OpenAI settings on your website. You are done. If you have purchased credits from the OpenAI API platform, it will start working.', 'chatbot'); ?></li>
            <li><?php  echo esc_html__('You can use different GPT Assistants on different pages with chatbot Widgets. Paste this sample shortcode and replace the Assistant ID as needed: Ex: [chatbot-widget hud_design="lite" gpt_assistant_id="assst_12345678"]', 'chatbot'); ?></li> 
        </ol>
        <strong><a href="https://woowbot.pro/docs/knowledgebase/how-to-train-ai-with-your-website-data-using-chatbot/" target="_blank"><?php echo esc_html__('Please check this article for step by step guide.', 'chatbot'); ?></a></strong>
        </p>
        
    </div>