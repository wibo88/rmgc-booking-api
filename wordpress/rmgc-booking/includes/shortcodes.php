<?php
// [Previous content remains the same until the nav styles]

        .custom-header nav span {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            color: transparent;
            cursor: pointer;
            background: rgba(255,255,255,0.2);
            border-radius: 4px;
            text-align: center;
            transition: background 0.3s, transform 0.2s;
        }
        .custom-header nav span:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-50%) scale(1.05);
        }
        .custom-header nav span:first-child {
            left: 15px;
        }
        .custom-header nav span:last-child {
            right: 15px;
        }
        .custom-header nav span:before {
            color: #fff;
            position: absolute;
            text-align: center;
            width: 100%;
            font-size: 20px;
            line-height: 32px;
            font-weight: bold;
        }
        .custom-header nav span.custom-prev:before {
            content: '‹';
        }
        .custom-header nav span.custom-next:before {
            content: '›';
        }

// [Rest of the file remains the same]